<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Services\AiDatabaseService;
use App\Models\User;

class TestDatabaseAi extends Command
{
    protected $signature = 'test:database-ai {user_id=1}';
    protected $description = 'Test AI responses with specific database questions';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Testing AI with Database-Specific Questions...');
        $this->newLine();

        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return 1;
        }

        $this->info("👤 Testing with user: {$user->username} (ID: {$userId})");
        $this->newLine();

        // First, show what database context is available
        $this->info('📊 Checking Database Context...');
        $databaseService = new AiDatabaseService();
        $roleData = $databaseService->getRoleBasedData($userId);
        
        $this->info('📄 Available data:');
        $this->line(json_encode($roleData, JSON_PRETTY_PRINT));
        $this->newLine();

        // Test specific database questions
        $questions = [
            "How many employees do we have in total?",
            "How many employees are present today?",
            "How many leave requests are pending?",
            "Which departments do we have?",
            "What's the attendance rate today?",
            "How many projects are currently active?"
        ];
        
        $chatService = new AiChatService();
        
        foreach ($questions as $index => $question) {
            $this->info("💬 Question " . ($index + 1) . ": \"{$question}\"");
            $this->info('⏳ Processing...');
            
            $result = $chatService->processMessage($userId, $question);
            
            if ($result['success']) {
                $this->info('✅ AI Response:');
                $this->line($result['message']);
                $this->newLine();
                
                // Check if response contains actual numbers
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
