# AI HR Chatbot Setup Guide

## Overview
This AI HR Chatbot provides intelligent assistance for HR-related queries using NVIDIA's Llama 3.3 70B model with OpenAI fallback. It's integrated directly into the Laravel HRM system without any external packages.

## Features
- **Intelligent HR Assistance**: Answers questions about policies, leave, attendance, etc.
- **Context-Aware Responses**: Personalized based on user role and department
- **Conversation History**: Maintains chat history for reference
- **Mobile Responsive**: Works seamlessly on all devices
- **Real-time Chat**: Instant responses with typing indicators
- **Secure**: Permission-based access and data protection

## Environment Setup

### 1. NVIDIA API Configuration (Primary)
Add the following variables to your `.env` file:

```env
# NVIDIA AI Configuration (Primary)
NVIDIA_API_KEY=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
NVIDIA_BASE_URL=https://integrate.api.nvidia.com/v1
NVIDIA_MODEL=nvidia/llama-3.3-nemotron-super-49b-v1
NVIDIA_MAX_TOKENS=4096
NVIDIA_TEMPERATURE=0.6
NVIDIA_TOP_P=0.95
NVIDIA_FREQUENCY_PENALTY=0
NVIDIA_PRESENCE_PENALTY=0

# OpenAI Configuration (Fallback - Optional)
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=500
OPENAI_TEMPERATURE=0.7
```

### 2. NVIDIA API Key (Already Provided)
The NVIDIA API key is already configured in the example above. This gives you access to:
- **Llama 3.3 70B Instruct** - Advanced language model
- **High-quality responses** - Better than GPT-3.5 for many tasks
- **Cost-effective** - Often more affordable than OpenAI

### 3. Optional: OpenAI Fallback
If you want a fallback option:
1. Visit [OpenAI Platform](https://platform.openai.com/)
2. Sign up or log in to your account
3. Navigate to API Keys section
4. Create a new API key
5. Add it to your `.env` file as `OPENAI_API_KEY`

### 3. Database Migration
Run the migration to create the necessary tables:

```bash
php artisan migrate
```

This will create:
- `ai_chat_conversations` - Stores conversation sessions
- `ai_chat_messages` - Stores individual messages

## Usage

### Accessing the AI Chat
1. Log in to the admin panel
2. Click on "AI HR Assistant" in the sidebar menu
3. Start typing your HR-related questions

### Example Questions
- "What is the company's leave policy?"
- "How do I apply for sick leave?"
- "What are my attendance statistics?"
- "Who is my department head?"
- "What benefits am I eligible for?"
- "How do I submit an expense report?"

### Features Available
- **New Conversations**: Start fresh chat sessions
- **Conversation History**: Access previous chats
- **Real-time Responses**: Instant AI replies
- **Context Awareness**: AI knows your role and department
- **Mobile Support**: Use on any device

## Technical Details

### API Endpoints
- `GET /ai-chat` - Chat interface
- `POST /ai-chat/send` - Send message
- `GET /ai-chat/conversation/{id}` - Get conversation
- `GET /ai-chat/conversations` - List conversations
- `POST /ai-chat/conversation/{id}/end` - End conversation

### Security
- All conversations are tied to authenticated users
- Messages are stored securely in the database
- API keys are protected in environment variables
- Input validation prevents malicious requests

### Customization
The AI system prompt can be customized in:
`app/Services/AiChatService.php` - `buildSystemPrompt()` method

## Troubleshooting

### Common Issues
1. **"API Key not found"** - Check your `.env` file has `OPENAI_API_KEY`
2. **"Rate limit exceeded"** - You've hit OpenAI's usage limits
3. **"Model not found"** - Check your `OPENAI_MODEL` setting
4. **"Database error"** - Run `php artisan migrate`

### Support
For technical support, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Browser console for JavaScript errors
3. Network tab for API request failures

## Cost Management
- Monitor your OpenAI usage at [OpenAI Usage Dashboard](https://platform.openai.com/usage)
- Set usage limits to control costs
- Consider using `gpt-3.5-turbo` for cost-effective operation
- Implement conversation limits if needed

## Future Enhancements
- Integration with company knowledge base
- Voice input/output capabilities
- Advanced analytics and reporting
- Multi-language support
- Custom AI training on company data
