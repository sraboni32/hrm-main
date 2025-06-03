<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Services\FullDatabaseService;
use App\Models\User;

class TestFixedDatabase extends Command
{
    protected $signature = 'test:fixed-database {user_id=1}';
    protected $description = 'Test fixed database access with department names';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Testing Fixed Database Access...');
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
        $this->info('📊 Testing Fixed Database Service:');
        $fullDbService = new FullDatabaseService();
        
        $this->info('1. System Statistics:');
        $stats = $fullDbService->getSystemStatistics();
        if (isset($stats['error'])) {
            $this->error('❌ Error: ' . $stats['error']);
            $this->info('📊 Fallback data: ' . json_encode($stats['fallback_data'], JSON_PRETTY_PRINT));
        } else {
            $this->info('✅ System stats working!');
            $this->line('Employees: ' . $stats['employees']['total']);
            $this->line('Departments: ' . $stats['departments']['total']);
            $this->line('Projects: ' . $stats['projects']['total']);
            $this->line('Tasks: ' . $stats['tasks']['total']);
        }
        $this->newLine();
        
        $this->info('2. Today\'s Attendance with Departments:');
        $attendance = $fullDbService->getTodayAttendanceSummary();
        $this->line('Present today: ' . $attendance['present_count']);
        $this->line('Total employees: ' . $attendance['total_employees']);
        
        if (isset($attendance['recent_attendance_records'])) {
            $this->info('Recent attendance records:');
            foreach (array_slice($attendance['recent_attendance_records'], 0, 3) as $record) {
                $this->line("- {$record['employee_name']} ({$record['department']}) - {$record['date']} {$record['clock_in']}");
            }
        }
        $this->newLine();
        
        $this->info('3. Department Information:');
        $departments = $fullDbService->getAllDepartments();
        $this->line('Total departments: ' . count($departments));
        foreach (array_slice($departments, 0, 3) as $dept) {
            $this->line("- {$dept['name']}: {$dept['employee_count']} employees, Head: {$dept['head']['name']}");
        }
        $this->newLine();

        // Now test through AI
        $this->info('🤖 Testing Through AI Agent:');
        $chatService = new AiChatService();
        
        $questions = [
            "Give me system overview",
            "Show me today's attendance with department names",
            "List recent attendance records with departments",
            "Show me all departments with employee counts"
        ];
        
        foreach ($questions as $index => $question) {
            $this->info("💬 Question " . ($index + 1) . ": \"{$question}\"");
            $this->info('⏳ Processing...');
            
            $result = $chatService->processMessage($userId, $question);
            
            if ($result['success']) {
                $this->info('✅ AI Response:');
                // Show first 400 characters
                $response = strlen($result['message']) > 400 ? 
                    substr($result['message'], 0, 400) . '...' : 
                    $result['message'];
                $this->line($response);
                $this->newLine();
                
                // Check for improvements
                if (preg_match('/Programming|Product|Marketing|Operations/i', $result['message'])) {
                    $this->info('✅ Contains department names - Fixed!');
                } else {
                    $this->warn('⚠️  No department names found');
                }
                
                if (preg_match('/\d+/', $result['message'])) {
                    $this->info('✅ Contains numbers');
                }
                
                if (strpos($result['message'], 'empty') === false && strpos($result['message'], '[]') === false) {
                    $this->info('✅ Contains data (not empty)');
                } else {
                    $this->warn('⚠️  Response indicates empty data');
                }
                
                $this->info('📊 Tokens: ' . ($result['metadata']['tokens_used'] ?? 'N/A'));
            } else {
                $this->error('❌ Failed: ' . $result['error']);
            }
            
            $this->newLine();
            $this->line('---');
            $this->newLine();
        }

        $this->info('🎉 Fixed database testing completed!');
        return 0;
    }
}
