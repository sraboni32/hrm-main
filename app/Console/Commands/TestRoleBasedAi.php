<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Services\AiDatabaseService;
use App\Models\User;

class TestRoleBasedAi extends Command
{
    protected $signature = 'test:role-based-ai {user_id=1}';
    protected $description = 'Test role-based AI responses with database context';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Testing Role-Based AI with Database Integration...');
        $this->newLine();

        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return 1;
        }

        $this->info("👤 Testing with user: {$user->username} (ID: {$userId})");
        $this->newLine();

        // Test database service
        $this->info('📊 Testing Database Service...');
        $databaseService = new AiDatabaseService();
        $roleData = $databaseService->getRoleBasedData($userId);
        
        $this->info('📄 Role-based data:');
        $this->line(json_encode($roleData, JSON_PRETTY_PRINT));
        $this->newLine();

        // Test different HR questions based on role
        $questions = $this->getQuestionsForRole($roleData['role'] ?? 'basic');
        
        $chatService = new AiChatService();
        
        foreach ($questions as $index => $question) {
            $this->info("💬 Question " . ($index + 1) . ": \"{$question}\"");
            $this->info('⏳ Processing...');
            
            $result = $chatService->processMessage($userId, $question);
            
            if ($result['success']) {
                $this->info('✅ Response:');
                $this->line($result['message']);
                $this->newLine();
                $this->info('📊 Tokens used: ' . ($result['metadata']['tokens_used'] ?? 'N/A'));
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

    private function getQuestionsForRole($role)
    {
        switch ($role) {
            case 'super_admin':
                return [
                    "How many employees do we have in total?",
                    "Which departments have the most employees?",
                    "What's our attendance rate today?",
                    "How many leave requests are pending approval?"
                ];
                
            case 'admin':
                return [
                    "How many people are in my department?",
                    "Who are my team members?",
                    "What projects is my department working on?",
                    "How can I approve leave requests?"
                ];
                
            case 'employee':
                return [
                    "How many vacation days do I have left?",
                    "What projects am I assigned to?",
                    "How do I request sick leave?",
                    "What's my attendance this month?"
                ];
                
            case 'client':
                return [
                    "What's the status of my projects?",
                    "How many projects do I have with the company?",
                    "Who should I contact for project updates?"
                ];
                
            default:
                return [
                    "Hello, can you help me with HR questions?",
                    "What services are available to me?"
                ];
        }
    }
}
