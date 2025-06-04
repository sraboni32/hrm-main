<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\ChatFile;
use App\Models\User;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Events\RoomDeleted;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show chat interface
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user's chat rooms
        $chatRooms = ChatRoom::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['members.user', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }])->orderBy('last_activity', 'desc')->get();

        // Get all users for direct messaging
        $users = User::where('id', '!=', $user->id)
            ->where('status', 1)
            ->select('id', 'username', 'avatar')
            ->get();

        return view('chat.index', compact('chatRooms', 'users'));
    }

    /**
     * Get messages for a chat room
     */
    public function getRoomMessages(Request $request, $roomId)
    {
        $user = Auth::user();
        $room = ChatRoom::findOrFail($roomId);

        // Check if user is member of this room
        if (!$room->hasMember($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $room->recentMessages($request->get('limit', 50));

        // Mark messages as read
        $room->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'room' => $room->load('members.user')
        ]);
    }

    /**
     * Get direct messages between two users
     */
    public function getDirectMessages(Request $request, $userId)
    {
        $currentUser = Auth::user();
        $otherUser = User::findOrFail($userId);

        $messages = ChatMessage::directMessagesBetween($currentUser->id, $userId)
            ->with(['sender', 'files'])
            ->orderBy('created_at', 'desc')
            ->limit($request->get('limit', 50))
            ->get()
            ->reverse()
            ->values();

        // Mark messages as read
        ChatMessage::directMessagesBetween($currentUser->id, $userId)
            ->where('sender_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'user' => $otherUser
        ]);
    }

    /**
     * Send message to chat room
     */
    public function sendRoomMessage(Request $request, $roomId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required_without:files|string|max:2000',
            'files.*' => 'file|max:10240', // 10MB max per file
            'reply_to_id' => 'nullable|exists:chat_messages,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $room = ChatRoom::findOrFail($roomId);

        // Check if user is member and can send messages
        $member = $room->members()->where('user_id', $user->id)->first();
        if (!$member || !$member->canSendMessages()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Create message
        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'sender_id' => $user->id,
            'message' => $request->message,
            'type' => $request->hasFile('files') ? 'file' : 'text',
            'reply_to_id' => $request->reply_to_id
        ]);

        // Handle file uploads
        if ($request->hasFile('files')) {
            $this->handleFileUploads($request->file('files'), $message);
        }

        // Update room activity
        $room->updateActivity();

        // Load relationships for response
        $message->load(['sender', 'files', 'replyTo']);

        // Broadcast message
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Send direct message
     */
    public function sendDirectMessage(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required_without:files|string|max:2000',
            'files.*' => 'file|max:10240', // 10MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sender = Auth::user();
        $receiver = User::findOrFail($userId);

        // Create message
        $message = ChatMessage::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $request->message,
            'type' => $request->hasFile('files') ? 'file' : 'text'
        ]);

        // Handle file uploads
        if ($request->hasFile('files')) {
            $this->handleFileUploads($request->file('files'), $message);
        }

        // Load relationships for response
        $message->load(['sender', 'receiver', 'files']);

        // Broadcast message
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Handle file uploads for messages
     */
    private function handleFileUploads($files, $message)
    {
        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('chat-files', $fileName, 'public');

            ChatFile::create([
                'chat_message_id' => $message->id,
                'original_name' => $originalName,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'file_hash' => hash_file('md5', $file->getRealPath())
            ]);
        }
    }

    /**
     * Create or get chat room
     */
    public function createRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:public,private,group,department',
            'members' => 'array',
            'members.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        $room = ChatRoom::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'created_by' => $user->id,
            'is_active' => true,
            'last_activity' => now()
        ]);

        // Add creator as admin
        $room->addMember($user->id, 'admin');

        // Add other members
        if ($request->has('members')) {
            foreach ($request->members as $memberId) {
                $room->addMember($memberId, 'member');
            }
        }

        return response()->json([
            'success' => true,
            'room' => $room->load('members.user')
        ]);
    }

    /**
     * Join chat room
     */
    public function joinRoom($roomId)
    {
        $user = Auth::user();
        $room = ChatRoom::findOrFail($roomId);

        if ($room->type === 'private') {
            return response()->json(['error' => 'Cannot join private room'], 403);
        }

        $room->addMember($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Joined room successfully'
        ]);
    }

    /**
     * Leave chat room
     */
    public function leaveRoom($roomId)
    {
        $user = Auth::user();
        $room = ChatRoom::findOrFail($roomId);

        $room->removeMember($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Left room successfully'
        ]);
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount()
    {
        $user = Auth::user();

        // Count unread room messages
        $roomUnread = ChatMessage::whereHas('chatRoom.members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('sender_id', '!=', $user->id)
          ->where('is_read', false)
          ->count();

        // Count unread direct messages
        $directUnread = ChatMessage::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'room_unread' => $roomUnread,
            'direct_unread' => $directUnread,
            'total_unread' => $roomUnread + $directUnread
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request)
    {
        $user = Auth::user();

        if ($request->has('room_id')) {
            // Mark room messages as read
            $room = ChatRoom::findOrFail($request->room_id);
            $room->messages()
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        } elseif ($request->has('user_id')) {
            // Mark direct messages as read
            ChatMessage::where('sender_id', $request->user_id)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * User typing indicator
     */
    public function typing(Request $request)
    {
        $user = Auth::user();

        broadcast(new UserTyping($user, $request->room_id, $request->user_id))->toOthers();

        return response()->json(['success' => true]);
    }

    /**
     * Delete chat room (Admin only)
     */
    public function deleteRoom($roomId)
    {
        Log::info("🗑️ Room deletion request received for room ID: {$roomId}");
        Log::info("📋 Request method: " . request()->method());
        Log::info("📋 Request headers: " . json_encode(request()->headers->all()));

        $user = Auth::user();
        if (!$user) {
            Log::error("❌ No authenticated user found");
            return response()->json(['error' => 'Authentication required'], 401);
        }

        Log::info("👤 User attempting deletion: {$user->id} ({$user->username})");

        try {
            $room = ChatRoom::findOrFail($roomId);
            Log::info("🏠 Room found: {$room->name} (ID: {$room->id})");
        } catch (\Exception $e) {
            Log::error("❌ Room not found: {$roomId}");
            return response()->json(['error' => 'Room not found'], 404);
        }

        // Check if user is admin of the room or super admin
        $member = $room->members()->where('user_id', $user->id)->first();
        $isSuperAdmin = $user->role_users_id == 1; // Super admin role

        Log::info("🔐 User role check - Member role: " . ($member ? $member->role : 'not a member') . ", Is super admin: " . ($isSuperAdmin ? 'yes' : 'no'));

        if (!$member || ($member->role !== 'admin' && !$isSuperAdmin)) {
            Log::warning("❌ Unauthorized deletion attempt by user {$user->id} for room {$roomId}");
            return response()->json(['error' => 'Unauthorized. Only room admins can delete rooms.'], 403);
        }

        try {
            // Store room info before deletion
            $roomName = $room->name;
            $roomId = $room->id;

            Log::info("📡 Broadcasting room deletion event for room: {$roomName}");
            // Broadcast room deletion event to all members before deleting
            try {
                broadcast(new RoomDeleted($roomId, $roomName, $user->username))->toOthers();
                Log::info("✅ Room deletion event broadcasted successfully");
            } catch (\Exception $e) {
                Log::warning("⚠️ Broadcasting failed, but continuing with deletion: " . $e->getMessage());
                // Continue with deletion even if broadcasting fails
            }

            Log::info("🗑️ Deleting room and all related data...");
            // Delete all related data (messages, files, members)
            $room->delete(); // This will cascade delete due to foreign key constraints

            Log::info("✅ Room '{$roomName}' deleted successfully by user {$user->username}");

            return response()->json([
                'success' => true,
                'message' => 'Chat room deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Failed to delete room {$roomId}: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete chat room',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get room details for admin actions
     */
    public function getRoomDetails($roomId)
    {
        $user = Auth::user();
        $room = ChatRoom::with(['members.user', 'creator'])->findOrFail($roomId);

        // Check if user is member of this room
        if (!$room->hasMember($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $member = $room->members()->where('user_id', $user->id)->first();
        $isSuperAdmin = $user->role_users_id == 1;

        return response()->json([
            'success' => true,
            'room' => $room,
            'user_role' => $member ? $member->role : 'member',
            'can_delete' => ($member && $member->role === 'admin') || $isSuperAdmin,
            'can_manage' => ($member && in_array($member->role, ['admin', 'moderator'])) || $isSuperAdmin
        ]);
    }

    /**
     * Download file
     */
    public function downloadFile($fileId)
    {
        $file = ChatFile::findOrFail($fileId);
        $user = Auth::user();

        // Check if user has access to this file
        $message = $file->chatMessage;

        if ($message->chat_room_id) {
            // Room message - check if user is member
            if (!$message->chatRoom->hasMember($user->id)) {
                abort(403);
            }
        } else {
            // Direct message - check if user is sender or receiver
            if ($message->sender_id !== $user->id && $message->receiver_id !== $user->id) {
                abort(403);
            }
        }

        return Storage::download($file->file_path, $file->original_name);
    }
}
