<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiChatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AiChatController extends Controller
{
    protected $aiChatService;

    public function __construct(AiChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    /**
     * Show the AI chat interface
     */
    public function index()
    {
        $user_auth = auth()->user();
        
        // Get user's recent conversations
        $conversations = $this->aiChatService->getUserConversations($user_auth->id);
        
        return view('ai_chat.index', compact('conversations'));
    }

    /**
     * Send message to AI
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'conversation_id' => 'nullable|integer|exists:ai_chat_conversations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $message = trim($request->input('message'));
        $conversationId = $request->input('conversation_id');

        try {
            $response = $this->aiChatService->processMessage(
                $user->id,
                $message,
                $conversationId
            );

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing your message. Please try again.'
            ], 500);
        }
    }

    /**
     * Get conversation history
     */
    public function getConversation(Request $request, $conversationId)
    {
        $user = Auth::user();
        
        $conversation = $this->aiChatService->getConversationHistory($user->id, $conversationId);
        
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'conversation' => $conversation
        ]);
    }

    /**
     * Get user's conversations list
     */
    public function getConversations()
    {
        $user = Auth::user();
        
        $conversations = $this->aiChatService->getUserConversations($user->id);
        
        return response()->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    /**
     * End/close a conversation
     */
    public function endConversation(Request $request, $conversationId)
    {
        $user = Auth::user();
        
        $result = $this->aiChatService->endConversation($user->id, $conversationId);
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Conversation ended successfully' : 'Failed to end conversation'
        ]);
    }

    /**
     * Start new conversation
     */
    public function newConversation()
    {
        return response()->json([
            'success' => true,
            'conversation_id' => null,
            'message' => 'New conversation started'
        ]);
    }
}
