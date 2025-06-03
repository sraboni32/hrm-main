<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Models\User;

class TestTasksAi extends Command
{
    protected $signature = 'test:tasks-ai {user_id=1}';
    protected $description = 'Test AI Agent with task queries';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Testing AI Agent with Task Queries...');
        $this->newLine();

        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return 1;
        }

        $this->info("👤 Testing with user: {$user->username} (ID: {$userId})");
        $this->newLine();

        // Test task-specific questions
        $questions = [
            "List all tasks",
            "What tasks do we have?",
            "Tell me task names",
            "Show me task details",
            "How many tasks are completed?",
            "What tasks are pending?",
            "Show me employee salaries",
            "Give me attendance details",
            "What are the leave requests?"
        ];
        
        $chatService = new AiChatService();
        
        foreach ($questions as $index => $question) {
            $this->info("💬 Question " . ($index + 1) . ": \"{$question}\"");
            $this->info('⏳ Processing...');
            
            $result = $chatService->processMessage($userId, $question);
            
            if ($result['success']) {
                $this->info('✅ AI Response:');
                // Show first 300 characters
                $response = strlen($result['message']) > 300 ? 
                    substr($result['message'], 0, 300) . '...' : 
                    $result['message'];
                $this->line($response);
                $this->newLine();
                
                // Check if response contains specific data
                if (preg_match('/\d+/', $result['message'])) {
                    $this->info('✅ Response contains specific numbers - Good!');
                } else {
                    $this->warn('⚠️  Response seems generic - may not be using database data');
                }
                
                $this->info('📊 Tokens: ' . ($result['metadata']['tokens_used'] ?? 'N/A'));
                $this->info('🔌 Provider: ' . ($result['metadata']['provider'] ?? 'N/A'));
            } else {
                $this->error('❌ Failed: ' . $result['error']);
            }
            
            $this->newLine();
            $this->line('---');
            $this->newLine();
        }

        return 0;
    }
}
