<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Models\User;

class TestProjectNames extends Command
{
    protected $signature = 'test:project-names {user_id=1}';
    protected $description = 'Test AI Agent with project name queries';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Testing AI Agent with Project Name Queries...');
        $this->newLine();

        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return 1;
        }

        $this->info("👤 Testing with user: {$user->username} (ID: {$userId})");
        $this->newLine();

        // Test specific project name questions
        $questions = [
            "Tell me project names",
            "What are all the project names?",
            "List all projects",
            "Show me all project details",
            "What projects do we have?",
            "Give me employee names",
            "List all employees",
            "What departments do we have?",
            "Show me department names"
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
                
                // Check if response contains specific names
                if (preg_match('/\b[A-Z][a-z]+\s+[A-Z][a-z]+\b/', $result['message'])) {
                    $this->info('✅ Response contains specific names - Good!');
                } else {
                    $this->warn('⚠️  Response may not contain specific names');
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
