<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Models\User;

class TestAdminAi extends Command
{
    protected $signature = 'test:admin-ai {user_id}';
    protected $description = 'Test AI Agent with admin-specific employee queries';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Testing AI Agent with Admin Employee Queries...');
        $this->newLine();

        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return 1;
        }

        $this->info("👤 Testing with user: {$user->username} (ID: {$userId})");
        $this->newLine();

        // Admin-specific test questions
        $questions = [
            "Show me all employees in my department",
            "List my team members with details",
            "What's the attendance rate in my department today?",
            "Show me department leave requests",
            "How many employees are on leave today in my department?",
            "What projects is my department working on?",
            "Show me department task assignments",
            "What's the average salary in my department?",
            "List all company employees",
            "Show me company-wide statistics",
            "What's the total payroll for my company?",
            "How many departments are in my company?"
        ];
        
        $chatService = new AiChatService();
        
        foreach ($questions as $index => $question) {
            $this->info("💬 Question " . ($index + 1) . ": \"{$question}\"");
            $this->info('⏳ Processing...');
            
            $result = $chatService->processMessage($userId, $question);
            
            if ($result['success']) {
                $this->info('✅ AI Response:');
                // Show first 400 characters to see detailed responses
                $response = strlen($result['message']) > 400 ? 
                    substr($result['message'], 0, 400) . '...' : 
                    $result['message'];
                $this->line($response);
                $this->newLine();
                
                // Check response quality
                if (preg_match('/\d+/', $result['message'])) {
                    $this->info('✅ Contains specific numbers');
                }
                if (preg_match('/\b[A-Z][a-z]+\s+[A-Z][a-z]+\b/', $result['message'])) {
                    $this->info('✅ Contains specific names');
                }
                if (preg_match('/department|team|company/i', $result['message'])) {
                    $this->info('✅ Contains department/company context');
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

        $this->info('🎉 Admin AI testing completed!');
        return 0;
    }
}
