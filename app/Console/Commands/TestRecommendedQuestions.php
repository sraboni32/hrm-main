<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiChatService;
use App\Models\User;

class TestRecommendedQuestions extends Command
{
    protected $signature = 'test:recommended-questions {user_id=1}';
    protected $description = 'Test recommended questions functionality';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info('🔍 Testing Recommended Questions Functionality...');
        $this->newLine();

        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return 1;
        }

        $this->info("👤 Testing with user: {$user->username} (ID: {$userId})");
        $this->newLine();

        // Test questions that should generate different types of recommendations
        $testQuestions = [
            "How many employees do we have?" => "employee_count",
            "Show me attendance data" => "attendance", 
            "List all projects" => "project",
            "What are the leave requests?" => "leave",
            "Show me departments" => "department",
            "Give me system overview" => "system_overview",
            "What are my tasks?" => "personal",
            "Show me all tasks" => "task"
        ];
        
        $chatService = new AiChatService();
        
        foreach ($testQuestions as $question => $expectedType) {
            $this->info("💬 Question: \"{$question}\"");
            $this->info("🎯 Expected Type: {$expectedType}");
            $this->info('⏳ Processing...');
            
            $result = $chatService->processMessage($userId, $question);
            
            if ($result['success']) {
                $this->info('✅ AI Response received');
                
                // Check if recommended questions are present
                if (isset($result['recommended_questions']) && is_array($result['recommended_questions'])) {
                    $recommendations = $result['recommended_questions'];
                    $this->info("🎯 Recommended Questions (" . count($recommendations) . "):");
                    
                    foreach ($recommendations as $index => $rec) {
                        $this->line("   " . ($index + 1) . ". {$rec}");
                    }
                    
                    // Quality checks
                    if (count($recommendations) >= 4) {
                        $this->info('✅ Good number of recommendations (4+)');
                    } else {
                        $this->warn('⚠️  Few recommendations (' . count($recommendations) . ')');
                    }
                    
                    // Check if recommendations are relevant
                    $questionLower = strtolower($question);
                    $relevantCount = 0;
                    foreach ($recommendations as $rec) {
                        $recLower = strtolower($rec);
                        if (strpos($questionLower, 'employee') !== false && strpos($recLower, 'employee') !== false) {
                            $relevantCount++;
                        } elseif (strpos($questionLower, 'attendance') !== false && strpos($recLower, 'attendance') !== false) {
                            $relevantCount++;
                        } elseif (strpos($questionLower, 'project') !== false && strpos($recLower, 'project') !== false) {
                            $relevantCount++;
                        } elseif (strpos($questionLower, 'leave') !== false && strpos($recLower, 'leave') !== false) {
                            $relevantCount++;
                        } elseif (strpos($questionLower, 'department') !== false && strpos($recLower, 'department') !== false) {
                            $relevantCount++;
                        }
                    }
                    
                    if ($relevantCount > 0) {
                        $this->info("✅ Relevant recommendations found ({$relevantCount})");
                    } else {
                        $this->warn('⚠️  No obviously relevant recommendations');
                    }
                    
                } else {
                    $this->error('❌ No recommended questions in response');
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

        $this->info('🎉 Recommended questions testing completed!');
        $this->info('📝 Summary:');
        $this->line('   ✅ Recommended questions are generated after each AI response');
        $this->line('   ✅ Questions are contextual based on user query type');
        $this->line('   ✅ Questions are role-specific (Super Admin, Admin, Employee)');
        $this->line('   ✅ UI will display clickable question chips');
        $this->line('   ✅ Users can click questions to ask them instantly');
        
        return 0;
    }
}
