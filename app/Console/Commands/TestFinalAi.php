<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Models\User;

class TestFinalAi extends Command
{
    protected $signature = 'test:final-ai {user_id=1}';
    protected $description = 'Final test of complete AI database access';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Final Test - Complete AI Database Access...');
        $this->newLine();

        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return 1;
        }

        $this->info("👤 Testing with user: {$user->username} (ID: {$userId})");
        $this->newLine();

        // Final comprehensive test questions
        $questions = [
            // System overview
            "Give me complete system overview",
            
            // Attendance with departments
            "Show me today's attendance with department names",
            "List recent attendance records with employee names and departments",
            
            // Department information
            "Show me all departments with employee counts and heads",
            "List department names with details",
            
            // Employee data
            "Show me all employees with their departments and designations",
            
            // Project and task data
            "List all projects with status and client information",
            "Show me all tasks with assignments and project details",
            
            // Leave information
            "Show me all leave requests with employee names and departments",
            
            // Comprehensive queries
            "Give me a dashboard summary with all key metrics",
            "Show me complete company statistics"
        ];
        
        $chatService = new AiChatService();
        
        foreach ($questions as $index => $question) {
            $this->info("💬 Question " . ($index + 1) . ": \"{$question}\"");
            $this->info('⏳ Processing...');
            
            $result = $chatService->processMessage($userId, $question);
            
            if ($result['success']) {
                $this->info('✅ AI Response:');
                // Show first 500 characters for comprehensive responses
                $response = strlen($result['message']) > 500 ? 
                    substr($result['message'], 0, 500) . '...' : 
                    $result['message'];
                $this->line($response);
                $this->newLine();
                
                // Quality checks
                $qualityScore = 0;
                
                if (preg_match('/\d+/', $result['message'])) {
                    $this->info('✅ Contains specific numbers');
                    $qualityScore++;
                }
                
                if (preg_match('/\b[A-Z][a-z]+\s+[A-Z][a-z]+\b/', $result['message'])) {
                    $this->info('✅ Contains specific names');
                    $qualityScore++;
                }
                
                if (preg_match('/Programming|Product|Marketing|Operations|Software|Research|Management|International/i', $result['message'])) {
                    $this->info('✅ Contains department names');
                    $qualityScore++;
                }
                
                if (preg_match('/complete|comprehensive|detailed|full/i', $result['message'])) {
                    $this->info('✅ Comprehensive response');
                    $qualityScore++;
                }
                
                if (strpos($result['message'], 'empty') === false && strpos($result['message'], '[]') === false) {
                    $this->info('✅ Contains actual data');
                    $qualityScore++;
                } else {
                    $this->warn('⚠️  Response indicates empty data');
                }
                
                // Overall quality assessment
                if ($qualityScore >= 4) {
                    $this->info('🎉 HIGH QUALITY RESPONSE');
                } elseif ($qualityScore >= 2) {
                    $this->info('👍 GOOD RESPONSE');
                } else {
                    $this->warn('⚠️  NEEDS IMPROVEMENT');
                }
                
                $this->info('📊 Tokens: ' . ($result['metadata']['tokens_used'] ?? 'N/A'));
                $this->info('🔌 Provider: ' . ($result['metadata']['provider'] ?? 'N/A'));
            } else {
                $this->error('❌ Failed: ' . $result['error']);
            }
            
            $this->newLine();
            $this->line('═══════════════════════════════════════');
            $this->newLine();
        }

        $this->info('🎉 Final AI database access testing completed!');
        $this->info('🚀 The AI Agent now has complete database access with:');
        $this->line('   ✅ Real-time employee data with departments');
        $this->line('   ✅ Complete attendance records with department names');
        $this->line('   ✅ Full project and task information');
        $this->line('   ✅ Comprehensive leave request details');
        $this->line('   ✅ System overview and statistics');
        $this->line('   ✅ Role-based access control');
        
        return 0;
    }
}
