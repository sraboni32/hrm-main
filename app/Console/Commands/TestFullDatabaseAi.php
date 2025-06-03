<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Models\User;

class TestFullDatabaseAi extends Command
{
    protected $signature = 'test:full-database-ai {user_id=1}';
    protected $description = 'Test AI Agent with full database access queries';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Testing AI Agent with Full Database Access...');
        $this->newLine();

        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return 1;
        }

        $this->info("👤 Testing with user: {$user->username} (ID: {$userId})");
        $this->newLine();

        // Full database access test questions
        $questions = [
            // Employee queries
            "Show me all employees with complete details",
            "Give me full employee information",
            "List all employees in the system",
            
            // Attendance queries
            "Show me all attendance records",
            "Give me complete attendance data",
            "What's today's attendance summary?",
            "Show me full attendance information",
            
            // Leave queries
            "Show me all leave requests",
            "Give me complete leave data",
            "List all leave requests with details",
            
            // Project queries
            "Show me all projects with complete details",
            "Give me full project information",
            "List all projects in the system",
            
            // Task queries
            "Show me all tasks with complete details",
            "Give me full task information",
            "List all tasks in the system",
            
            // Department queries
            "Show me all departments with complete details",
            "Give me full department information",
            
            // System overview
            "Give me system overview",
            "Show me dashboard summary",
            "What's the complete system status?"
        ];
        
        $chatService = new AiChatService();
        
        foreach ($questions as $index => $question) {
            $this->info("💬 Question " . ($index + 1) . ": \"{$question}\"");
            $this->info('⏳ Processing...');
            
            $result = $chatService->processMessage($userId, $question);
            
            if ($result['success']) {
                $this->info('✅ AI Response:');
                // Show first 500 characters to see comprehensive responses
                $response = strlen($result['message']) > 500 ? 
                    substr($result['message'], 0, 500) . '...' : 
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
                if (preg_match('/complete|full|comprehensive|detailed/i', $result['message'])) {
                    $this->info('✅ Contains comprehensive data indicators');
                }
                if (preg_match('/email|phone|department|designation/i', $result['message'])) {
                    $this->info('✅ Contains detailed employee information');
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

        $this->info('🎉 Full database AI testing completed!');
        return 0;
    }
}
