@extends('layouts.master')

@section('main-content')
@section('page-css')
<style>
.ai-chat-container {
    height: calc(100vh - 200px);
    display: flex;
    flex-direction: column;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 20px;
}

.message {
    margin-bottom: 15px;
    display: flex;
    align-items: flex-start;
}

.message.user {
    justify-content: flex-end;
}

.message.assistant {
    justify-content: flex-start;
}

.message-content {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 18px;
    word-wrap: break-word;
    position: relative;
}

.message.user .message-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.message.assistant .message-content {
    background: white;
    color: #333;
    border: 1px solid #e0e0e0;
    border-bottom-left-radius: 4px;
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    margin: 0 8px;
}

.message.user .message-avatar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    order: 2;
}

.message.assistant .message-avatar {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.chat-input-container {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.chat-input {
    flex: 1;
    border: 2px solid #e0e0e0;
    border-radius: 25px;
    padding: 12px 20px;
    resize: none;
    max-height: 120px;
    min-height: 50px;
}

.chat-input:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.send-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s;
}

.send-button:hover {
    transform: scale(1.05);
}

.send-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.typing-indicator {
    display: none;
    align-items: center;
    gap: 8px;
    color: #666;
    font-style: italic;
    margin-bottom: 15px;
}

.typing-dots {
    display: flex;
    gap: 4px;
}

.typing-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #667eea;
    animation: typing 1.4s infinite ease-in-out;
}

.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

.conversation-sidebar {
    background: white;
    border-radius: 10px;
    padding: 20px;
    height: calc(100vh - 200px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.conversation-list {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 5px;
    margin-right: -5px;
}

/* Custom scrollbar for conversation list */
.conversation-list::-webkit-scrollbar {
    width: 6px;
}

.conversation-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.conversation-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.conversation-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.conversation-item {
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 8px;
    transition: background 0.2s;
    border: 1px solid #f0f0f0;
}

.conversation-item:hover {
    background: #f8f9fa;
}

.conversation-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.new-chat-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px;
    width: 100%;
    margin-bottom: 20px;
    cursor: pointer;
    transition: transform 0.2s;
}

.new-chat-btn:hover {
    transform: translateY(-2px);
}

/* Recommended Questions Styles */
.recommended-questions {
    margin-top: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.recommended-questions h6 {
    color: #495057;
    margin-bottom: 12px;
    font-weight: 600;
    font-size: 14px;
}

.question-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.question-chip {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 8px 16px;
    font-size: 13px;
    color: #495057;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    word-break: break-word;
}

.question-chip:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.question-chip i {
    margin-right: 6px;
    font-size: 12px;
}

@media (max-width: 768px) {
    .conversation-sidebar {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        z-index: 1050;
        background: white;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .conversation-sidebar.show {
        display: flex;
        transform: translateX(0);
    }

    .message-content {
        max-width: 85%;
    }

    .question-chip {
        font-size: 12px;
        padding: 6px 12px;
    }

    .conversation-list {
        padding-right: 10px;
        margin-right: -10px;
    }

    /* Mobile sidebar backdrop */
    .sidebar-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .sidebar-backdrop.show {
        opacity: 1;
        visibility: visible;
    }
}
</style>
@endsection

@section('main-content')

<div class="breadcrumb">
    <h1>{{ __('translate.AI_HR_Assistant') }}</h1>
    <ul>
        <li>{{ __('translate.Dashboard') }}</li>
        <li>{{ __('translate.AI_Chat') }}</li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>

<div class="row" id="ai-chat-app">
    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop d-md-none" :class="{ show: sidebarVisible }" @click="toggleSidebar"></div>

    <!-- Conversations Sidebar -->
    <div class="col-lg-3 col-md-4 d-none d-md-block">
        <div class="conversation-sidebar" :class="{ show: sidebarVisible }">
            <button class="new-chat-btn" @click="startNewConversation">
                <i class="i-Add mr-2"></i>
                {{ __('translate.New_Conversation') }}
            </button>

            <div class="conversation-list">
                <div v-for="conv in conversations"
                     :key="conv.id"
                     class="conversation-item"
                     :class="{ active: currentConversationId === conv.id }"
                     @click="loadConversation(conv.id)">
                    <div class="font-weight-bold">@{{ conv.title }}</div>
                    <small class="text-muted">@{{ conv.last_message }}</small>
                    <small class="text-muted d-block">@{{ conv.updated_at }}</small>
                </div>

                <!-- Show message when no conversations -->
                <div v-if="conversations.length === 0" class="text-center text-muted py-4">
                    <i class="i-Chat" style="font-size: 24px;"></i>
                    <p class="mt-2 mb-0">{{ __('translate.No_conversations_yet') }}</p>
                    <small>{{ __('translate.Start_chatting_to_see_history') }}</small>
                </div>
            </div>

            <!-- Close button for mobile -->
            <button class="btn btn-sm btn-outline-secondary d-md-none mt-3" @click="toggleSidebar">
                <i class="i-Close mr-1"></i>
                {{ __('translate.Close') }}
            </button>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="col-lg-9 col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="i-Robot mr-2 text-primary"></i>
                    {{ __('translate.AI_HR_Assistant') }}
                </h5>
                <button class="btn btn-sm btn-outline-primary d-md-none" @click="toggleSidebar">
                    <i class="i-Menu"></i>
                </button>
            </div>

            <div class="card-body p-0">
                <div class="ai-chat-container">
                    <!-- Messages Area -->
                    <div class="chat-messages" ref="messagesContainer">
                        <!-- Welcome Message -->
                        <div v-if="messages.length === 0" class="text-center text-muted py-5">
                            <i class="i-Robot" style="font-size: 48px; color: #667eea;"></i>
                            <h4 class="mt-3">{{ __('translate.Welcome_to_AI_HR_Assistant') }}</h4>
                            <p>{{ __('translate.Ask_me_anything_about_HR') }}</p>

                            <!-- Welcome Recommended Questions -->
                            <div class="recommended-questions mt-4" style="max-width: 600px; margin: 20px auto;">
                                <h6><i class="i-Light-Bulb mr-1"></i>{{ __('translate.Try_asking') }}:</h6>
                                <div class="question-chips">
                                    <div class="question-chip" @click="askRecommendedQuestion('Give me system overview')">
                                        <i class="i-Dashboard"></i>
                                        {{ __('translate.System_Overview') }}
                                    </div>
                                    <div class="question-chip" @click="askRecommendedQuestion('Show me today\'s attendance')">
                                        <i class="i-Calendar"></i>
                                        {{ __('translate.Today_Attendance') }}
                                    </div>
                                    <div class="question-chip" @click="askRecommendedQuestion('List all employees')">
                                        <i class="i-Users"></i>
                                        {{ __('translate.All_Employees') }}
                                    </div>
                                    <div class="question-chip" @click="askRecommendedQuestion('Show me project status')">
                                        <i class="i-Folder"></i>
                                        {{ __('translate.Project_Status') }}
                                    </div>
                                    <div class="question-chip" @click="askRecommendedQuestion('What are pending leave requests?')">
                                        <i class="i-Time"></i>
                                        {{ __('translate.Leave_Requests') }}
                                    </div>
                                    <div class="question-chip" @click="askRecommendedQuestion('Show me department information')">
                                        <i class="i-Building"></i>
                                        {{ __('translate.Departments') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div v-for="message in messages" :key="message.id" :class="['message', message.type]">
                            <div class="message-avatar">
                                <span v-if="message.type === 'user'">@{{ userInitials }}</span>
                                <i v-else class="i-Robot"></i>
                            </div>
                            <div class="message-content">
                                <div v-html="formatMessage(message.message)"></div>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                    @{{ formatTime(message.created_at) }}
                                </small>

                                <!-- Recommended Questions (only for assistant messages) -->
                                <div v-if="message.type === 'assistant' && message.recommended_questions && message.recommended_questions.length > 0"
                                     class="recommended-questions">
                                    <h6><i class="i-Light-Bulb mr-1"></i>{{ __('translate.You_might_also_ask') }}:</h6>
                                    <div class="question-chips">
                                        <div v-for="question in message.recommended_questions"
                                             :key="question"
                                             class="question-chip"
                                             @click="askRecommendedQuestion(question)">
                                            <i class="i-Cursor"></i>
                                            @{{ question }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Typing Indicator -->
                        <div class="typing-indicator" v-show="isTyping">
                            <div class="message-avatar">
                                <i class="i-Robot"></i>
                            </div>
                            <div class="message-content">
                                <span>{{ __('translate.AI_is_thinking') }}</span>
                                <div class="typing-dots">
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="p-3">
                        <div class="chat-input-container">
                            <textarea
                                v-model="currentMessage"
                                @keydown.enter.prevent="sendMessage"
                                @input="autoResize"
                                ref="messageInput"
                                class="chat-input"
                                placeholder="{{ __('translate.Type_your_HR_question_here') }}"
                                :disabled="isTyping"
                                rows="1"></textarea>

                            <button
                                @click="sendMessage"
                                :disabled="!currentMessage.trim() || isTyping"
                                class="send-button">
                                <i class="i-Cursor" v-if="!isTyping"></i>
                                <div class="spinner-border spinner-border-sm" v-else></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-js')
<script>
var app = new Vue({
    el: '#ai-chat-app',
    data: {
        messages: [],
        conversations: @json($conversations),
        currentMessage: '',
        currentConversationId: null,
        isTyping: false,
        userInitials: '{{ strtoupper(substr(auth()->user()->username ?? "US", 0, 2)) }}',
        sidebarVisible: false
    },

    mounted() {
        this.scrollToBottom();
        this.$refs.messageInput.focus();
    },

    methods: {
        sendMessage() {
            if (!this.currentMessage.trim() || this.isTyping) return;

            const message = this.currentMessage.trim();
            this.currentMessage = '';

            // Add user message to UI immediately
            this.addMessage('user', message, new Date().toISOString());

            // Show typing indicator
            this.isTyping = true;
            this.scrollToBottom();

            // Send to backend
            axios.post('/ai-chat/send', {
                message: message,
                conversation_id: this.currentConversationId
            })
            .then(response => {
                this.isTyping = false;

                if (response.data.success) {
                    // Update conversation ID if new conversation
                    if (response.data.conversation_id && !this.currentConversationId) {
                        this.currentConversationId = response.data.conversation_id;
                        this.loadConversations(); // Refresh sidebar
                    }

                    // Add AI response with recommended questions
                    this.addMessage('assistant', response.data.message, new Date().toISOString(), response.data.recommended_questions);
                } else {
                    // Show debug information in development
                    let errorMessage = response.data.error || 'Sorry, I encountered an error. Please try again.';
                    if (response.data.debug_error) {
                        console.error('AI Chat Debug Error:', response.data.debug_error);
                        // In development, you can uncomment the next line to see the actual error
                        // errorMessage += '\n\nDebug: ' + response.data.debug_error;
                    }
                    this.addMessage('assistant', errorMessage, new Date().toISOString());
                }
            })
            .catch(error => {
                this.isTyping = false;
                console.error('Chat error:', error);
                this.addMessage('assistant', 'I apologize, but I\'m experiencing technical difficulties. Please try again later.', new Date().toISOString());
            });
        },

        addMessage(type, content, timestamp, recommendedQuestions = null) {
            this.messages.push({
                id: Date.now(),
                type: type,
                message: content,
                created_at: timestamp,
                recommended_questions: recommendedQuestions
            });

            this.$nextTick(() => {
                this.scrollToBottom();
            });
        },

        askRecommendedQuestion(question) {
            this.currentMessage = question;
            this.sendMessage();
        },

        loadConversation(conversationId) {
            this.currentConversationId = conversationId;
            this.messages = [];

            // Close sidebar on mobile after selecting conversation
            if (window.innerWidth <= 768) {
                this.sidebarVisible = false;
            }

            axios.get(`/ai-chat/conversation/${conversationId}`)
            .then(response => {
                if (response.data.success) {
                    this.messages = response.data.conversation.messages;
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                }
            })
            .catch(error => {
                console.error('Error loading conversation:', error);
                toastr.error('Failed to load conversation');
            });
        },

        startNewConversation() {
            this.currentConversationId = null;
            this.messages = [];
            this.$refs.messageInput.focus();
        },

        loadConversations() {
            axios.get('/ai-chat/conversations')
            .then(response => {
                if (response.data.success) {
                    this.conversations = response.data.conversations;
                }
            })
            .catch(error => {
                console.error('Error loading conversations:', error);
            });
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        autoResize() {
            const textarea = this.$refs.messageInput;
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        },

        formatMessage(message) {
            // Simple formatting for line breaks and basic markdown
            return message
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>');
        },

        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        toggleSidebar() {
            this.sidebarVisible = !this.sidebarVisible;
        }
    }
});
</script>
@endsection