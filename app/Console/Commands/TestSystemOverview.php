<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Services\FullDatabaseService;
use App\Models\User;

class TestSystemOverview extends Command
{
    protected $signature = 'test:system-overview {user_id=1}';
    protected $description = 'Test system overview and missing data';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Testing System Overview and Missing Data...');
        $this->newLine();

        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return 1;
        }

        $this->info("👤 Testing with user: {$user->username} (ID: {$userId})");
        $this->newLine();

        // Test direct database service first
        $this->info('📊 Testing Direct Database Service:');
        $fullDbService = new FullDatabaseService();
        
        $this->info('1. System Statistics:');
        $stats = $fullDbService->getSystemStatistics();
        $this->line(json_encode($stats, JSON_PRETTY_PRINT));
        $this->newLine();
        
        $this->info('2. Today\'s Attendance:');
        $attendance = $fullDbService->getTodayAttendanceSummary();
        $this->line(json_encode($attendance, JSON_PRETTY_PRINT));
        $this->newLine();
        
        $this->info('3. All Leave Requests:');
        $leaves = $fullDbService->getAllLeaveRequests(5); // Just 5 for testing
        $this->line('Leave count: ' . count($leaves));
        if (!empty($leaves)) {
            $this->line('First leave: ' . json_encode($leaves[0], JSON_PRETTY_PRINT));
        }
        $this->newLine();

        // Now test through AI
        $this->info('🤖 Testing Through AI Agent:');
        $chatService = new AiChatService();
        
        $questions = [
            "Give me system overview",
            "Show me today's attendance with details",
            "List all leave requests with employee names"
        ];
        
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
                
                // Check if response contains data
                if (preg_match('/\d+/', $result['message'])) {
                    $this->info('✅ Contains numbers');
                } else {
                    $this->warn('⚠️  No numbers found');
                }
                
                if (strpos($result['message'], 'empty') !== false || strpos($result['message'], '[]') !== false) {
                    $this->warn('⚠️  Response indicates empty data');
                } else {
                    $this->info('✅ Response contains data');
                }
                
                $this->info('📊 Tokens: ' . ($result['metadata']['tokens_used'] ?? 'N/A'));
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
