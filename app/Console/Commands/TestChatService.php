<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TestChatService extends Command
{
    protected $signature = 'test:chat-service {user_id=1}';
    protected $description = 'Test the actual AiChatService with a real user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Testing AiChatService Flow...');
        $this->newLine();

        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return 1;
        }

        $this->info("👤 Testing with user: {$user->username} (ID: {$userId})");
        $this->info("🔑 User role: " . ($user->role_users_id == 1 ? 'Admin' : 'Employee'));
        $this->newLine();

        try {
            // Test service instantiation
            $this->info('🔧 Instantiating AiChatService...');
            $chatService = new AiChatService();
            $this->info('✅ Service instantiated successfully');
            $this->newLine();

            // Test message processing
            $testMessage = "Hello! Can you help me with HR questions?";
            $this->info("💬 Testing message: \"{$testMessage}\"");
            $this->info('⏳ Processing message...');
            $this->newLine();

            $result = $chatService->processMessage($userId, $testMessage);

            $this->info('📊 Service Response:');
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
            $this->newLine();

            if ($result['success']) {
                $this->info('✅ Chat Service Test SUCCESSFUL!');
                $this->info('🤖 AI Response: ' . $result['message']);
                $this->info('💾 Conversation ID: ' . $result['conversation_id']);
                
                if (isset($result['metadata']['provider'])) {
                    $this->info('🔌 Provider: ' . $result['metadata']['provider']);
                }
                
                $this->newLine();
                $this->info('🎉 The chat service is working correctly!');
                $this->info('The issue might be in the web controller or frontend.');
                
            } else {
                $this->error('❌ Chat Service Test FAILED!');
                $this->error('Error: ' . $result['error']);
                
                if (isset($result['debug_error'])) {
                    $this->error('Debug: ' . $result['debug_error']);
                }
                
                $this->newLine();
                $this->info('🔍 Check the Laravel logs for more details:');
                $this->info('tail -f storage/logs/laravel.log');
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception in chat service test:');
            $this->error($e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            $this->newLine();
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());
            
            Log::error('Chat Service Test Exception', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return 0;
    }
}
