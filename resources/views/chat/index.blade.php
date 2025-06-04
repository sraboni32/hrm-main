@extends('layouts.master')

@section('main-content')
<div id="chat-app">
    <div class="breadcrumb">
        <h1>{{ __('Real-time Chat') }}</h1>
        <ul>
            <li><a href="/dashboard/admin">{{ __('Dashboard') }}</a></li>
            <li>{{ __('Chat') }}</li>
        </ul>
    </div>

    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="chat-container">
                        <!-- Chat Sidebar -->
                        <div class="chat-sidebar">
                            <div class="chat-sidebar-header">
                                <div class="header-content">
                                    <h5>{{ __('Conversations') }}</h5>
                                    <div class="connection-status" :class="{ connected: isConnected }">
                                        <i class="fas fa-circle"></i>
                                        <span>@{{ isConnected ? 'Connected' : 'Connecting...' }}</span>
                                    </div>
                                </div>
                                <button class="btn btn-primary btn-sm" @click="showCreateRoomModal = true">
                                    <i class="fas fa-plus"></i> {{ __('New Room') }}
                                </button>
                            </div>

                            <!-- Chat Rooms -->
                            <div class="chat-rooms-section">
                                <h6>{{ __('Chat Rooms') }}</h6>
                                <div class="chat-room-list">
                                    <div v-for="room in chatRooms" :key="'room-' + room.id" 
                                         class="chat-item" 
                                         :class="{ active: activeRoom && activeRoom.id === room.id }"
                                         @click="selectRoom(room)">
                                        <div class="chat-item-avatar">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="chat-item-content">
                                            <div class="chat-item-name">@{{ room.name }}</div>
                                            <div class="chat-item-last-message">
                                                @{{ room.last_message || 'No messages yet' }}
                                            </div>
                                        </div>
                                        <div class="chat-item-meta">
                                            <span v-if="room.unread_count > 0" class="badge badge-primary">
                                                @{{ room.unread_count }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Direct Messages -->
                            <div class="direct-messages-section">
                                <h6>{{ __('Direct Messages') }}</h6>
                                <div class="user-list">
                                    <div v-for="user in users" :key="'user-' + user.id" 
                                         class="chat-item" 
                                         :class="{ active: activeUser && activeUser.id === user.id }"
                                         @click="selectUser(user)">
                                        <div class="chat-item-avatar">
                                            <img :src="'/assets/images/avatar/' + user.avatar"
                                                 :alt="user.username" class="avatar-img">
                                            <span class="online-status" :class="{ online: user.is_online }">
                                                <i class="fas fa-circle" v-if="user.is_online"></i>
                                            </span>
                                        </div>
                                        <div class="chat-item-content">
                                            <div class="chat-item-name">@{{ user.username }}</div>
                                            <div class="chat-item-last-message">
                                                @{{ user.last_message || 'Start a conversation' }}
                                            </div>
                                        </div>
                                        <div class="chat-item-meta">
                                            <span v-if="user.unread_count > 0" class="badge badge-primary">
                                                @{{ user.unread_count }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Main Area -->
                        <div class="chat-main">
                            <div v-if="!activeRoom && !activeUser" class="chat-welcome">
                                <div class="welcome-content">
                                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                    <h4>{{ __('Welcome to Real-time Chat') }}</h4>
                                    <p>{{ __('Select a chat room or user to start messaging') }}</p>
                                </div>
                            </div>

                            <div v-else class="chat-active">
                                <!-- Chat Header -->
                                <div class="chat-header">
                                    <div class="chat-header-info">
                                        <div class="chat-header-avatar">
                                            <i v-if="activeRoom" class="fas fa-users"></i>
                                            <img v-else-if="activeUser" 
                                                 :src="'/assets/images/avatar/' + activeUser.avatar" 
                                                 :alt="activeUser.username" class="avatar-img">
                                        </div>
                                        <div class="chat-header-details">
                                            <h5>@{{ activeRoom ? activeRoom.name : activeUser.username }}</h5>
                                            <span v-if="activeRoom" class="text-muted">
                                                @{{ activeRoom.members ? activeRoom.members.length : 0 }} members
                                            </span>
                                            <span v-else-if="activeUser" class="text-muted">
                                                @{{ activeUser.is_online ? 'Online' : 'Offline' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="chat-header-actions">
                                        <button v-if="activeRoom" class="btn btn-sm btn-outline-secondary"
                                                @click="showRoomManagement">
                                            <i class="fas fa-cog"></i> {{ __('Manage') }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Messages Area -->
                                <div class="chat-messages" ref="messagesContainer">
                                    <div v-for="message in messages" :key="message.id" class="message-wrapper">
                                        <div class="message" 
                                             :class="{ 'message-own': message.sender_id === currentUserId }">
                                            <div class="message-avatar">
                                                <img :src="'/assets/images/avatar/' + message.sender.avatar" 
                                                     :alt="message.sender.username" class="avatar-img">
                                            </div>
                                            <div class="message-content">
                                                <div class="message-header">
                                                    <span class="message-sender">@{{ message.sender.username }}</span>
                                                    <span class="message-time">@{{ formatTime(message.created_at) }}</span>
                                                </div>
                                                <div class="message-body">
                                                    <div v-if="message.reply_to_id" class="message-reply">
                                                        <i class="fas fa-reply"></i>
                                                        Replying to @{{ message.reply_to ? message.reply_to.sender.username : 'deleted message' }}
                                                    </div>
                                                    <div v-if="message.type === 'text'" class="message-text">
                                                        @{{ message.message }}
                                                    </div>
                                                    <div v-else-if="message.files && message.files.length > 0" class="message-files">
                                                        <div v-for="file in message.files" :key="file.id" class="file-attachment">
                                                            <div v-if="file.is_image" class="image-preview">
                                                                <img :src="file.url" :alt="file.original_name" 
                                                                     class="img-thumbnail" style="max-width: 200px;">
                                                            </div>
                                                            <div v-else class="file-info">
                                                                <i :class="file.icon"></i>
                                                                <span>@{{ file.original_name }}</span>
                                                                <small>(@{{ file.human_file_size }})</small>
                                                            </div>
                                                            <a :href="'/chat/download/' + file.id" 
                                                               class="btn btn-sm btn-outline-primary" download>
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Typing Indicator -->
                                    <div v-if="typingUsers.length > 0" class="typing-indicator">
                                        <div class="typing-dots">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                        </div>
                                        <span class="typing-text">
                                            @{{ getTypingText() }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Message Input -->
                                <div class="chat-input">
                                    <form @submit.prevent="sendMessage" enctype="multipart/form-data">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label class="btn btn-outline-secondary" for="file-upload">
                                                    <i class="fas fa-paperclip"></i>
                                                </label>
                                                <input type="file" id="file-upload" multiple 
                                                       @change="handleFileSelect" style="display: none;">
                                            </div>
                                            <input type="text" v-model="newMessage" 
                                                   @keyup="handleTyping"
                                                   class="form-control" 
                                                   :placeholder="'Type a message...'" 
                                                   :disabled="sending">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-primary" :disabled="!canSend">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- File Preview -->
                                    <div v-if="selectedFiles.length > 0" class="file-preview">
                                        <div v-for="(file, index) in selectedFiles" :key="index" class="file-preview-item">
                                            <span>@{{ file.name }}</span>
                                            <button type="button" @click="removeFile(index)" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Room Modal -->
    <div class="modal fade" :class="{ show: showCreateRoomModal }" 
         :style="{ display: showCreateRoomModal ? 'block' : 'none' }"
         tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Create New Chat Room') }}</h5>
                    <button type="button" class="close" @click="showCreateRoomModal = false">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="createRoom">
                        <div class="form-group">
                            <label>{{ __('Room Name') }}</label>
                            <input type="text" v-model="newRoom.name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Description') }}</label>
                            <textarea v-model="newRoom.description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Room Type') }}</label>
                            <select v-model="newRoom.type" class="form-control">
                                <option value="public">{{ __('Public') }}</option>
                                <option value="private">{{ __('Private') }}</option>
                                <option value="group">{{ __('Group') }}</option>
                                <option value="department">{{ __('Department') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Add Members') }}</label>
                            <select v-model="newRoom.members" class="form-control" multiple>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    @{{ user.username }}
                                </option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showCreateRoomModal = false">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" @click="createRoom" :disabled="!newRoom.name">
                        {{ __('Create Room') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Management Modal -->
    <div class="modal fade" :class="{ show: showRoomManagementModal }"
         :style="{ display: showRoomManagementModal ? 'block' : 'none' }"
         tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Manage Room') }}: @{{ activeRoom ? activeRoom.name : '' }}</h5>
                    <button type="button" class="close" @click="closeRoomManagement">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" v-if="roomDetails">
                    <div class="room-info">
                        <h6>{{ __('Room Information') }}</h6>
                        <p><strong>{{ __('Name') }}:</strong> @{{ roomDetails.room.name }}</p>
                        <p><strong>{{ __('Description') }}:</strong> @{{ roomDetails.room.description || 'No description' }}</p>
                        <p><strong>{{ __('Type') }}:</strong> @{{ roomDetails.room.type }}</p>
                        <p><strong>{{ __('Created by') }}:</strong> @{{ roomDetails.room.creator ? roomDetails.room.creator.username : 'Unknown' }}</p>
                        <p><strong>{{ __('Members') }}:</strong> @{{ roomDetails.room.members ? roomDetails.room.members.length : 0 }}</p>
                        <p><strong>{{ __('Your Role') }}:</strong>
                            <span class="badge" :class="getRoleBadgeClass(roomDetails.user_role)">
                                @{{ roomDetails.user_role }}
                            </span>
                        </p>
                    </div>

                    <div class="room-members mt-4" v-if="roomDetails.room.members">
                        <h6>{{ __('Members') }}</h6>
                        <div class="members-list">
                            <div v-for="member in roomDetails.room.members" :key="member.id" class="member-item">
                                <img :src="'/assets/images/avatar/' + member.user.avatar"
                                     :alt="member.user.username" class="member-avatar">
                                <div class="member-info">
                                    <span class="member-name">@{{ member.user.username }}</span>
                                    <span class="member-role badge" :class="getRoleBadgeClass(member.role)">
                                        @{{ member.role }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone for Admins -->
                    <div class="danger-zone mt-4" v-if="roomDetails.can_delete">
                        <h6 class="text-danger">{{ __('Danger Zone') }}</h6>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ __('Deleting this room will permanently remove all messages, files, and member data. This action cannot be undone.') }}
                        </div>
                        <button class="btn btn-danger" @click="confirmDeleteRoom">
                            <i class="fas fa-trash"></i> {{ __('Delete Room') }}
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeRoomManagement">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" :class="{ show: showDeleteConfirmModal }"
         :style="{ display: showDeleteConfirmModal ? 'block' : 'none' }"
         tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> {{ __('Confirm Delete') }}
                    </h5>
                    <button type="button" class="close text-white" @click="cancelDeleteRoom">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-trash fa-3x text-danger"></i>
                    </div>
                    <h6>{{ __('Are you sure you want to delete this room?') }}</h6>
                    <p class="text-muted">
                        {{ __('This will permanently delete') }} "<strong>@{{ activeRoom ? activeRoom.name : '' }}</strong>"
                        {{ __('and all its messages, files, and member data.') }}
                    </p>
                    <p class="text-danger font-weight-bold">{{ __('This action cannot be undone!') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="cancelDeleteRoom">
                        <i class="fas fa-times"></i> {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" @click="deleteRoom" :disabled="deleting">
                        <i class="fas fa-trash"></i>
                        @{{ deleting ? 'Deleting...' : 'Delete Room' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
.chat-container {
    display: flex;
    height: 80vh;
    min-height: 600px;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
}

.chat-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    z-index: 1;
}

.chat-container > * {
    position: relative;
    z-index: 2;
}

.chat-sidebar {
    width: 320px;
    background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
    border-right: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    backdrop-filter: blur(20px);
}

.chat-sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.header-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.chat-sidebar-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 1.2rem;
}

.connection-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    opacity: 0.8;
}

.connection-status i {
    font-size: 0.75rem;
    color: #dc3545;
    animation: pulse 2s infinite;
}

.connection-status.connected i {
    color: #28a745;
    animation: none;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.chat-rooms-section, .direct-messages-section {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.chat-rooms-section h6, .direct-messages-section h6 {
    margin-bottom: 0.5rem;
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    font-weight: 600;
}

.chat-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    cursor: pointer;
    border-radius: 0.375rem;
    margin-bottom: 0.25rem;
    transition: background-color 0.15s ease-in-out;
}

.chat-item:hover {
    background-color: #e9ecef;
}

.chat-item.active {
    background-color: #007bff;
    color: white;
}

.chat-item-avatar {
    position: relative;
    margin-right: 0.75rem;
}

.chat-item-avatar .avatar-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.chat-item-avatar i {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #6c757d;
    color: white;
    border-radius: 50%;
}

.online-status {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #dc3545;
    border: 2px solid white;
}

.online-status.online {
    background: #28a745;
}

.chat-item-content {
    flex: 1;
    min-width: 0;
}

.chat-item-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-item-last-message {
    font-size: 0.875rem;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-item.active .chat-item-last-message {
    color: rgba(255, 255, 255, 0.8);
}

.chat-item-meta {
    margin-left: 0.5rem;
}

.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.chat-welcome {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}

.welcome-content {
    text-align: center;
}

.chat-active {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.chat-header {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
}

.chat-header-info {
    display: flex;
    align-items: center;
}

.chat-header-avatar {
    margin-right: 0.75rem;
}

.chat-header-avatar .avatar-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.chat-header-avatar i {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #007bff;
    color: white;
    border-radius: 50%;
}

.chat-header-details h5 {
    margin: 0;
    margin-bottom: 0.25rem;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    background: #f8f9fa;
}

.message-wrapper {
    margin-bottom: 1rem;
}

.message {
    display: flex;
    align-items: flex-start;
}

.message-own {
    flex-direction: row-reverse;
}

.message-avatar {
    margin: 0 0.75rem;
}

.message-avatar .avatar-img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.message-content {
    max-width: 70%;
    background: white;
    border-radius: 0.75rem;
    padding: 0.75rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-own .message-content {
    background: #007bff;
    color: white;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.message-sender {
    font-weight: 600;
    font-size: 0.875rem;
}

.message-time {
    font-size: 0.75rem;
    color: #6c757d;
}

.message-own .message-time {
    color: rgba(255, 255, 255, 0.8);
}

.message-reply {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
    padding: 0.25rem 0.5rem;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 0.25rem;
}

.message-own .message-reply {
    color: rgba(255, 255, 255, 0.8);
    background: rgba(255, 255, 255, 0.1);
}

.file-attachment {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    padding: 0.5rem;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 0.25rem;
}

.message-own .file-attachment {
    background: rgba(255, 255, 255, 0.1);
}

.image-preview img {
    border-radius: 0.25rem;
}

.typing-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    color: #6c757d;
}

.typing-dots {
    display: flex;
    gap: 0.25rem;
}

.typing-dots span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #6c757d;
    animation: typing 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) { animation-delay: -0.32s; }
.typing-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

.chat-input {
    padding: 1rem;
    border-top: 1px solid #dee2e6;
    background: white;
}

.file-preview {
    margin-top: 0.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.file-preview-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    background: #e9ecef;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

/* Room Management Modal Styles */
.room-info p {
    margin-bottom: 0.5rem;
}

.members-list {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.5rem;
}

.member-item {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.15s ease-in-out;
}

.member-item:last-child {
    border-bottom: none;
}

.member-item:hover {
    background-color: #f8f9fa;
}

.member-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 0.75rem;
}

.member-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.member-name {
    font-weight: 500;
    font-size: 0.875rem;
}

.member-role {
    font-size: 0.75rem;
}

.badge-admin {
    background-color: #dc3545;
    color: white;
}

.badge-moderator {
    background-color: #fd7e14;
    color: white;
}

.badge-member {
    background-color: #6c757d;
    color: white;
}

.danger-zone {
    border: 2px solid #dc3545;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #f8f9fa;
}

.danger-zone h6 {
    margin-bottom: 1rem;
}

/* Delete Confirmation Modal */
.modal-sm .modal-content {
    border-radius: 0.5rem;
}

.modal-header.bg-danger {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
}

/* Modal backdrop */
.modal.show {
    background-color: rgba(0, 0, 0, 0.5);
}

@media (max-width: 768px) {
    .chat-container {
        flex-direction: column;
        height: auto;
    }

    .chat-sidebar {
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
    }

    .message-content {
        max-width: 85%;
    }

    .members-list {
        max-height: 150px;
    }
}
</style>
@endsection

@section('page-js')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
<script>
// Initialize Laravel Echo
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '{{ env("PUSHER_APP_KEY") }}',
    cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
    forceTLS: false,
    wsHost: window.location.hostname,
    wsPort: 6001,
    wssPort: 6001,
    disableStats: true,
    enabledTransports: ['ws', 'wss']
});

console.log('🚀 Laravel Echo initialized');
</script>
<script>
new Vue({
    el: '#chat-app',
    data: {
        currentUserId: {{ auth()->id() }},
        chatRooms: @json($chatRooms),
        users: @json($users),
        activeRoom: null,
        activeUser: null,
        messages: [],
        newMessage: '',
        selectedFiles: [],
        sending: false,
        typingUsers: [],
        typingTimeout: null,
        showCreateRoomModal: false,
        showRoomManagementModal: false,
        showDeleteConfirmModal: false,
        isConnected: false,
        roomDetails: null,
        deleting: false,
        newRoom: {
            name: '',
            description: '',
            type: 'public',
            members: []
        }
    },

    computed: {
        canSend() {
            return (this.newMessage.trim() || this.selectedFiles.length > 0) && !this.sending;
        }
    },

    mounted() {
        this.initializeWebSocket();
        this.loadUnreadCounts();

        // Auto-scroll to bottom when new messages arrive
        this.$nextTick(() => {
            this.scrollToBottom();
        });
    },

    methods: {
        initializeWebSocket() {
            // Initialize Laravel Echo for real-time functionality
            if (window.Echo) {
                console.log('🚀 Initializing WebSocket connection...');

                // Handle connection events
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    console.log('✅ WebSocket connected');
                    this.isConnected = true;
                });

                window.Echo.connector.pusher.connection.bind('disconnected', () => {
                    console.log('❌ WebSocket disconnected');
                    this.isConnected = false;
                });

                window.Echo.connector.pusher.connection.bind('error', (error) => {
                    console.error('❌ WebSocket error:', error);
                    this.isConnected = false;
                });

                // Listen for direct messages to current user
                window.Echo.private(`user.${this.currentUserId}`)
                    .listen('.message.sent', (e) => {
                        console.log('📨 New message received:', e);
                        this.handleNewMessage(e.message);
                        this.playNotificationSound();
                        this.showNotification(e.message);
                    })
                    .listen('.user.typing', (e) => {
                        this.handleTypingEvent(e);
                    });

                // Listen for user online/offline status
                window.Echo.join(`online-users`)
                    .here((users) => {
                        console.log('👥 Users currently online:', users);
                        this.updateOnlineUsers(users);
                    })
                    .joining((user) => {
                        console.log('✅ User joined:', user);
                        this.updateUserStatus(user.id, true);
                    })
                    .leaving((user) => {
                        console.log('❌ User left:', user);
                        this.updateUserStatus(user.id, false);
                    });
            } else {
                console.warn('⚠️ Laravel Echo not available');
            }
        },

        subscribeToRoom(roomId) {
            if (window.Echo && roomId) {
                console.log(`🏠 Subscribing to room: ${roomId}`);
                window.Echo.private(`chat-room.${roomId}`)
                    .listen('.message.sent', (e) => {
                        console.log('📨 Room message received:', e);
                        if (this.activeRoom && this.activeRoom.id === roomId) {
                            this.handleNewMessage(e.message);
                            this.playNotificationSound();
                        }
                    })
                    .listen('.user.typing', (e) => {
                        if (this.activeRoom && this.activeRoom.id === roomId) {
                            this.handleTypingEvent(e);
                        }
                    })
                    .listen('.room.deleted', (e) => {
                        console.log('🗑️ Room deleted:', e);
                        this.handleRoomDeleted(e);
                    });
            }
        },

        unsubscribeFromRoom(roomId) {
            if (window.Echo && roomId) {
                console.log(`🚪 Unsubscribing from room: ${roomId}`);
                window.Echo.leave(`chat-room.${roomId}`);
            }
        },

        selectRoom(room) {
            // Unsubscribe from previous room
            if (this.activeRoom) {
                this.unsubscribeFromRoom(this.activeRoom.id);
            }

            this.activeRoom = room;
            this.activeUser = null;
            this.messages = [];
            this.loadRoomMessages(room.id);
            this.markAsRead({ room_id: room.id });

            // Subscribe to new room
            this.subscribeToRoom(room.id);
        },

        selectUser(user) {
            this.activeUser = user;
            this.activeRoom = null;
            this.messages = [];
            this.loadDirectMessages(user.id);
            this.markAsRead({ user_id: user.id });
        },

        loadRoomMessages(roomId) {
            axios.get(`/chat/room/${roomId}/messages`)
                .then(response => {
                    if (response.data.success) {
                        this.messages = response.data.messages;
                        this.$nextTick(() => this.scrollToBottom());
                    }
                })
                .catch(error => {
                    console.error('Error loading room messages:', error);
                });
        },

        loadDirectMessages(userId) {
            axios.get(`/chat/direct/${userId}/messages`)
                .then(response => {
                    if (response.data.success) {
                        this.messages = response.data.messages;
                        this.$nextTick(() => this.scrollToBottom());
                    }
                })
                .catch(error => {
                    console.error('Error loading direct messages:', error);
                });
        },

        sendMessage() {
            if (!this.canSend) return;

            this.sending = true;
            const formData = new FormData();

            if (this.newMessage.trim()) {
                formData.append('message', this.newMessage);
            }

            // Add files
            this.selectedFiles.forEach((file, index) => {
                formData.append(`files[${index}]`, file);
            });

            const url = this.activeRoom
                ? `/chat/room/${this.activeRoom.id}/send`
                : `/chat/direct/${this.activeUser.id}/send`;

            axios.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(response => {
                if (response.data.success) {
                    this.messages.push(response.data.message);
                    this.newMessage = '';
                    this.selectedFiles = [];
                    this.$nextTick(() => this.scrollToBottom());
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                this.$toastr.error('Failed to send message');
            })
            .finally(() => {
                this.sending = false;
            });
        },

        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            this.selectedFiles = [...this.selectedFiles, ...files];
        },

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
        },

        handleTyping() {
            // Send typing indicator
            const data = this.activeRoom
                ? { room_id: this.activeRoom.id }
                : { user_id: this.activeUser.id };

            axios.post('/chat/typing', data);

            // Clear existing timeout
            if (this.typingTimeout) {
                clearTimeout(this.typingTimeout);
            }

            // Set new timeout to stop typing indicator
            this.typingTimeout = setTimeout(() => {
                // Stop typing indicator logic here
            }, 3000);
        },

        handleTypingEvent(event) {
            // Add user to typing list
            const existingIndex = this.typingUsers.findIndex(u => u.id === event.user.id);
            if (existingIndex === -1) {
                this.typingUsers.push(event.user);
            }

            // Remove user from typing list after 3 seconds
            setTimeout(() => {
                const index = this.typingUsers.findIndex(u => u.id === event.user.id);
                if (index !== -1) {
                    this.typingUsers.splice(index, 1);
                }
            }, 3000);
        },

        getTypingText() {
            if (this.typingUsers.length === 1) {
                return `${this.typingUsers[0].username} is typing...`;
            } else if (this.typingUsers.length === 2) {
                return `${this.typingUsers[0].username} and ${this.typingUsers[1].username} are typing...`;
            } else if (this.typingUsers.length > 2) {
                return `${this.typingUsers.length} people are typing...`;
            }
            return '';
        },

        createRoom() {
            axios.post('/chat/room/create', this.newRoom)
                .then(response => {
                    if (response.data.success) {
                        this.chatRooms.unshift(response.data.room);
                        this.showCreateRoomModal = false;
                        this.newRoom = {
                            name: '',
                            description: '',
                            type: 'public',
                            members: []
                        };
                        this.$toastr.success('Chat room created successfully');
                    }
                })
                .catch(error => {
                    console.error('Error creating room:', error);
                    this.$toastr.error('Failed to create chat room');
                });
        },

        loadUnreadCounts() {
            axios.get('/chat/unread-count')
                .then(response => {
                    if (response.data.success) {
                        // Update unread counts in UI
                        // This would be implemented based on your specific needs
                    }
                })
                .catch(error => {
                    console.error('Error loading unread counts:', error);
                });
        },

        markAsRead(data) {
            axios.post('/chat/mark-read', data)
                .catch(error => {
                    console.error('Error marking as read:', error);
                });
        },

        scrollToBottom() {
            if (this.$refs.messagesContainer) {
                this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
            }
        },

        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        // Enhanced real-time methods
        handleNewMessage(message) {
            // Check if message belongs to current conversation
            if (this.activeRoom && message.chat_room_id === this.activeRoom.id) {
                this.messages.push(message);
                this.$nextTick(() => this.scrollToBottom());
            } else if (this.activeUser &&
                      ((message.sender_id === this.activeUser.id && message.receiver_id === this.currentUserId) ||
                       (message.sender_id === this.currentUserId && message.receiver_id === this.activeUser.id))) {
                this.messages.push(message);
                this.$nextTick(() => this.scrollToBottom());
            }

            // Update unread counts
            this.updateUnreadCounts();
        },

        playNotificationSound() {
            // Create notification sound
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
            audio.volume = 0.3;
            audio.play().catch(() => {
                // Ignore if audio play fails (browser restrictions)
            });
        },

        showNotification(message) {
            if (Notification.permission === 'granted') {
                const notification = new Notification(`New message from ${message.sender.username}`, {
                    body: message.message || 'Sent a file',
                    icon: `/assets/images/avatar/${message.sender.avatar}`,
                    tag: 'chat-message'
                });

                notification.onclick = () => {
                    window.focus();
                    notification.close();
                };

                setTimeout(() => notification.close(), 5000);
            }
        },

        updateOnlineUsers(users) {
            users.forEach(user => {
                this.updateUserStatus(user.id, true);
            });
        },

        updateUserStatus(userId, isOnline) {
            // Update user status in users list
            const userIndex = this.users.findIndex(u => u.id === userId);
            if (userIndex !== -1) {
                this.$set(this.users[userIndex], 'is_online', isOnline);
            }

            // Update status in chat rooms
            this.chatRooms.forEach(room => {
                if (room.members) {
                    const memberIndex = room.members.findIndex(m => m.user_id === userId);
                    if (memberIndex !== -1) {
                        this.$set(room.members[memberIndex], 'is_online', isOnline);
                    }
                }
            });
        },

        updateUnreadCounts() {
            // This would typically fetch from server
            // For now, we'll implement a simple client-side counter
        },

        requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },

        // Room Management Methods
        showRoomManagement() {
            if (!this.activeRoom) {
                console.log('❌ No active room selected');
                return;
            }

            console.log('🔧 Opening room management for room:', this.activeRoom.id, this.activeRoom.name);
            this.loadRoomDetails(this.activeRoom.id);
        },

        loadRoomDetails(roomId) {
            console.log('📡 Loading room details for room ID:', roomId);
            axios.get(`/chat/room/${roomId}/details`)
                .then(response => {
                    console.log('✅ Room details response:', response.data);
                    if (response.data.success) {
                        this.roomDetails = response.data;
                        this.showRoomManagementModal = true;
                        console.log('🔧 Room management modal opened');
                    }
                })
                .catch(error => {
                    console.error('❌ Error loading room details:', error);
                    if (this.$toastr) {
                        this.$toastr.error('Failed to load room details');
                    } else {
                        alert('Failed to load room details');
                    }
                });
        },

        closeRoomManagement() {
            this.showRoomManagementModal = false;
            this.roomDetails = null;
        },

        confirmDeleteRoom() {
            this.showDeleteConfirmModal = true;
        },

        cancelDeleteRoom() {
            this.showDeleteConfirmModal = false;
        },

        deleteRoom() {
            if (!this.activeRoom || this.deleting) return;

            console.log('🗑️ Starting room deletion for room:', this.activeRoom.id);
            this.deleting = true;

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('🔐 CSRF Token:', csrfToken ? 'Found' : 'Not found');

            // Use axios with proper configuration
            axios({
                method: 'DELETE',
                url: `/chat/room/${this.activeRoom.id}/delete`,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    console.log('✅ Room deletion response:', response.data);
                    if (response.data.success) {
                        // Remove room from chat rooms list
                        const roomIndex = this.chatRooms.findIndex(r => r.id === this.activeRoom.id);
                        console.log('📍 Room index to remove:', roomIndex);
                        if (roomIndex !== -1) {
                            this.chatRooms.splice(roomIndex, 1);
                            console.log('✅ Room removed from list');
                        }

                        // Unsubscribe from room before clearing
                        this.unsubscribeFromRoom(this.activeRoom.id);

                        // Clear active room and messages
                        this.activeRoom = null;
                        this.messages = [];

                        // Close modals
                        this.showDeleteConfirmModal = false;
                        this.showRoomManagementModal = false;
                        this.roomDetails = null;

                        // Show success message
                        if (this.$toastr) {
                            this.$toastr.success('Room deleted successfully');
                        } else {
                            alert('Room deleted successfully');
                        }

                        console.log('✅ Room deletion completed successfully');
                    }
                })
                .catch(error => {
                    console.error('❌ Error deleting room:', error);
                    console.error('❌ Error response:', error.response);
                    console.error('❌ Error status:', error.response?.status);
                    console.error('❌ Error data:', error.response?.data);

                    let errorMessage = 'Failed to delete room';
                    if (error.response?.status === 403) {
                        errorMessage = 'You do not have permission to delete this room';
                    } else if (error.response?.status === 404) {
                        errorMessage = 'Room not found';
                    } else if (error.response?.status === 419) {
                        errorMessage = 'Session expired. Please refresh the page and try again';
                    } else if (error.response?.data?.error) {
                        errorMessage = error.response.data.error;
                    } else if (error.response?.data?.message) {
                        errorMessage = error.response.data.message;
                    }

                    if (this.$toastr) {
                        this.$toastr.error(errorMessage);
                    } else {
                        alert(errorMessage);
                    }
                })
                .finally(() => {
                    this.deleting = false;
                    console.log('🏁 Room deletion process finished');
                });
        },

        getRoleBadgeClass(role) {
            switch (role) {
                case 'admin':
                    return 'badge-admin';
                case 'moderator':
                    return 'badge-moderator';
                case 'member':
                default:
                    return 'badge-member';
            }
        },

        handleRoomDeleted(event) {
            // Remove room from chat rooms list
            const roomIndex = this.chatRooms.findIndex(r => r.id === event.room_id);
            if (roomIndex !== -1) {
                this.chatRooms.splice(roomIndex, 1);
            }

            // If this is the currently active room, clear it
            if (this.activeRoom && this.activeRoom.id === event.room_id) {
                this.activeRoom = null;
                this.messages = [];

                // Close any open modals
                this.showRoomManagementModal = false;
                this.showDeleteConfirmModal = false;
                this.roomDetails = null;
            }

            // Show notification
            this.$toastr.warning(`Room "${event.room_name}" was deleted by ${event.deleted_by}`);

            // Play notification sound
            this.playNotificationSound();
        }
    },

    mounted() {
        this.initializeWebSocket();
        this.loadUnreadCounts();
        this.requestNotificationPermission();

        // Auto-scroll to bottom when new messages arrive
        this.$nextTick(() => {
            this.scrollToBottom();
        });
    }
});
</script>
@endsection
