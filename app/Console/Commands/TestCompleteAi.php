<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Models\User;

class TestCompleteAi extends Command
{
    protected $signature = 'test:complete-ai {user_id=1}';
    protected $description = 'Test AI Agent with comprehensive data queries';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Testing Complete AI Agent System...');
        $this->newLine();

        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return 1;
        }

        $this->info("👤 Testing with user: {$user->username} (ID: {$userId})");
        $this->newLine();

        // Comprehensive test questions
        $testCategories = [
            'Projects' => [
                "Tell me project names",
                "What are all the project details?",
                "Show me project status breakdown"
            ],
            'Tasks' => [
                "List all tasks",
                "What tasks do we have?",
                "Show me task details",
                "Tell me task names"
            ],
            'Employees' => [
                "Give me employee names",
                "List all employees with details",
                "Show me employee information"
            ],
            'Departments' => [
                "What departments do we have?",
                "Show me department details",
                "List department names"
            ],
            'Attendance' => [
                "Show me attendance details",
                "What's today's attendance information?",
                "Give me attendance statistics"
            ],
            'Leave' => [
                "Show me leave details",
                "What are all the leave requests?",
                "Give me leave information"
            ],
            'Salary' => [
                "Show me salary details",
                "What are employee salaries?",
                "Give me salary information"
            ]
        ];
        
        $chatService = new AiChatService();
        
        foreach ($testCategories as $category => $questions) {
            $this->info("🔸 Testing {$category} Queries:");
            $this->newLine();
            
            foreach ($questions as $index => $question) {
                $this->info("💬 Question: \"{$question}\"");
                $this->info('⏳ Processing...');
                
                $result = $chatService->processMessage($userId, $question);
                
                if ($result['success']) {
                    $this->info('✅ AI Response:');
                    // Show first 200 characters to avoid overwhelming output
                    $response = strlen($result['message']) > 200 ? 
                        substr($result['message'], 0, 200) . '...' : 
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
                    
                    $this->info('📊 Tokens: ' . ($result['metadata']['tokens_used'] ?? 'N/A'));
                } else {
                    $this->error('❌ Failed: ' . $result['error']);
                }
                
                $this->newLine();
                $this->line('---');
                $this->newLine();
            }
            
            $this->line('═══════════════════════════════════════');
            $this->newLine();
        }

        $this->info('🎉 Complete AI Agent testing finished!');
        return 0;
    }
}
