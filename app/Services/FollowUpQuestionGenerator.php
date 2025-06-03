<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Follow-Up Question Generator - Dynamic Question Generation Based on Context
 * 
 * Features:
 * - Context-aware follow-up generation
 * - Result-based question suggestions
 * - Smart question categorization
 * - Personalized suggestions
 * - Trending topic integration
 */
class FollowUpQuestionGenerator
{
    private $questionPatterns;
    private $contextualRules;
    private $smartSuggestions;

    public function __construct()
    {
        $this->initializeQuestionPatterns();
        $this->initializeContextualRules();
        $this->initializeSmartSuggestions();
    }

    /**
     * Generate intelligent follow-up questions
     */
    public function generateFollowUps($originalQuestion, $queryResult, $semanticAnalysis = null, $userContext = [])
    {
        try {
            $followUps = [];

            // 1. Analyze the original question
            $questionAnalysis = $this->analyzeOriginalQuestion($originalQuestion);
            
            // 2. Analyze the query results
            $resultAnalysis = $this->analyzeQueryResults($queryResult);
            
            // 3. Generate pattern-based follow-ups
            $patternFollowUps = $this->generatePatternBasedFollowUps($questionAnalysis, $resultAnalysis);
            $followUps = array_merge($followUps, $patternFollowUps);

            // 4. Generate data-driven follow-ups
            $dataFollowUps = $this->generateDataDrivenFollowUps($queryResult, $questionAnalysis);
            $followUps = array_merge($followUps, $dataFollowUps);

            // 5. Generate contextual follow-ups
            $contextualFollowUps = $this->generateContextualFollowUps($questionAnalysis, $resultAnalysis, $userContext);
            $followUps = array_merge($followUps, $contextualFollowUps);

            // 6. Generate semantic follow-ups if available
            if ($semanticAnalysis) {
                $semanticFollowUps = $this->generateSemanticBasedFollowUps($semanticAnalysis, $resultAnalysis);
                $followUps = array_merge($followUps, $semanticFollowUps);
            }

            // 7. Generate smart suggestions
            $smartFollowUps = $this->generateSmartSuggestions($questionAnalysis, $resultAnalysis);
            $followUps = array_merge($followUps, $smartFollowUps);

            // 8. Remove duplicates and rank by relevance
            $uniqueFollowUps = $this->removeDuplicatesAndRank($followUps);

            // 9. Limit and format results
            return $this->formatFollowUpResults($uniqueFollowUps, 6);

        } catch (\Exception $e) {
            Log::error('Follow-up generation error: ' . $e->getMessage());
            return $this->getFallbackFollowUps($originalQuestion);
        }
    }

    /**
     * Analyze the original question for context
     */
    private function analyzeOriginalQuestion($question)
    {
        $analysis = [
            'type' => 'general',
            'intent' => 'unknown',
            'entities' => [],
            'scope' => 'general',
            'complexity' => 'simple',
            'time_context' => null,
            'department_context' => null,
            'employee_context' => null
        ];

        $question = strtolower($question);

        // Determine question type
        $typePatterns = [
            'count' => ['how many', 'count', 'total', 'number of'],
            'list' => ['show', 'list', 'display', 'get all', 'find'],
            'comparison' => ['compare', 'vs', 'versus', 'difference between'],
            'trend' => ['trend', 'over time', 'growth', 'change'],
            'status' => ['status', 'current', 'active', 'pending'],
            'analytics' => ['analyze', 'analysis', 'statistics', 'metrics'],
            'personal' => ['my', 'mine', 'i have', 'i am'],
            'filter' => ['where', 'with', 'having', 'filter by']
        ];

        foreach ($typePatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($question, $pattern) !== false) {
                    $analysis['type'] = $type;
                    break 2;
                }
            }
        }

        // Determine intent
        $intentPatterns = [
            'information' => ['show', 'display', 'what', 'which', 'who'],
            'analysis' => ['analyze', 'compare', 'trend', 'pattern'],
            'monitoring' => ['status', 'current', 'today', 'now'],
            'planning' => ['upcoming', 'future', 'next', 'plan'],
            'reporting' => ['report', 'summary', 'overview', 'dashboard']
        ];

        foreach ($intentPatterns as $intent => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($question, $pattern) !== false) {
                    $analysis['intent'] = $intent;
                    break 2;
                }
            }
        }

        // Extract entities
        $entityPatterns = [
            'employees' => ['employee', 'staff', 'worker', 'team member'],
            'departments' => ['department', 'dept', 'division'],
            'projects' => ['project', 'initiative'],
            'tasks' => ['task', 'assignment', 'work'],
            'attendance' => ['attendance', 'present', 'absent'],
            'leaves' => ['leave', 'vacation', 'holiday'],
            'salary' => ['salary', 'pay', 'compensation'],
            'performance' => ['performance', 'rating', 'review']
        ];

        foreach ($entityPatterns as $entity => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($question, $pattern) !== false) {
                    $analysis['entities'][] = $entity;
                }
            }
        }

        // Determine time context
        $timePatterns = [
            'today' => ['today', 'now'],
            'this_week' => ['this week', 'current week'],
            'this_month' => ['this month', 'current month'],
            'this_year' => ['this year', 'current year'],
            'yesterday' => ['yesterday'],
            'last_week' => ['last week', 'previous week'],
            'last_month' => ['last month', 'previous month']
        ];

        foreach ($timePatterns as $timeContext => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($question, $pattern) !== false) {
                    $analysis['time_context'] = $timeContext;
                    break 2;
                }
            }
        }

        return $analysis;
    }

    /**
     * Analyze query results for follow-up context
     */
    private function analyzeQueryResults($queryResult)
    {
        $analysis = [
            'has_data' => false,
            'record_count' => 0,
            'data_types' => [],
            'contains_aggregation' => false,
            'contains_dates' => false,
            'contains_numbers' => false,
            'contains_status' => false,
            'table_context' => [],
            'field_analysis' => []
        ];

        if (!isset($queryResult['data']) || empty($queryResult['data'])) {
            return $analysis;
        }

        $analysis['has_data'] = true;
        $analysis['record_count'] = count($queryResult['data']);

        // Analyze first record structure
        $firstRecord = (array) $queryResult['data'][0];
        
        foreach ($firstRecord as $field => $value) {
            $fieldLower = strtolower($field);
            
            // Check for dates
            if (strpos($fieldLower, 'date') !== false || strpos($fieldLower, 'time') !== false) {
                $analysis['contains_dates'] = true;
            }
            
            // Check for numbers
            if (is_numeric($value)) {
                $analysis['contains_numbers'] = true;
            }
            
            // Check for status fields
            if (strpos($fieldLower, 'status') !== false || strpos($fieldLower, 'state') !== false) {
                $analysis['contains_status'] = true;
            }
            
            // Check for aggregation indicators
            if (strpos($fieldLower, 'count') !== false || strpos($fieldLower, 'total') !== false || 
                strpos($fieldLower, 'avg') !== false || strpos($fieldLower, 'sum') !== false) {
                $analysis['contains_aggregation'] = true;
            }

            // Determine table context
            if (strpos($fieldLower, 'employee') !== false || strpos($fieldLower, 'firstname') !== false) {
                $analysis['table_context'][] = 'employees';
            }
            if (strpos($fieldLower, 'department') !== false) {
                $analysis['table_context'][] = 'departments';
            }
            if (strpos($fieldLower, 'project') !== false) {
                $analysis['table_context'][] = 'projects';
            }
        }

        $analysis['table_context'] = array_unique($analysis['table_context']);

        return $analysis;
    }

    /**
     * Generate pattern-based follow-ups
     */
    private function generatePatternBasedFollowUps($questionAnalysis, $resultAnalysis)
    {
        $followUps = [];
        $questionType = $questionAnalysis['type'];
        $intent = $questionAnalysis['intent'];

        // Type-based patterns
        switch ($questionType) {
            case 'count':
                if ($resultAnalysis['has_data']) {
                    $followUps[] = ['text' => 'Show me the detailed breakdown of these numbers', 'category' => 'drill_down'];
                    $followUps[] = ['text' => 'How does this compare to last month?', 'category' => 'comparison'];
                    $followUps[] = ['text' => 'What are the trends over time?', 'category' => 'trend'];
                }
                break;

            case 'list':
                if ($resultAnalysis['record_count'] > 5) {
                    $followUps[] = ['text' => 'Show me the top 10 results only', 'category' => 'filter'];
                    $followUps[] = ['text' => 'Sort these results by different criteria', 'category' => 'sort'];
                }
                if ($resultAnalysis['contains_dates']) {
                    $followUps[] = ['text' => 'Group these results by month', 'category' => 'group'];
                }
                break;

            case 'analytics':
                $followUps[] = ['text' => 'Show me the key insights from this analysis', 'category' => 'insights'];
                $followUps[] = ['text' => 'What recommendations do you have?', 'category' => 'recommendations'];
                $followUps[] = ['text' => 'Compare this to industry benchmarks', 'category' => 'benchmark'];
                break;

            case 'personal':
                $followUps[] = ['text' => 'Show my historical performance', 'category' => 'history'];
                $followUps[] = ['text' => 'Compare my metrics to team average', 'category' => 'comparison'];
                $followUps[] = ['text' => 'What are my upcoming deadlines?', 'category' => 'planning'];
                break;
        }

        // Intent-based patterns
        switch ($intent) {
            case 'information':
                $followUps[] = ['text' => 'Show me related information', 'category' => 'related'];
                $followUps[] = ['text' => 'Provide more details about this data', 'category' => 'details'];
                break;

            case 'monitoring':
                $followUps[] = ['text' => 'Set up alerts for changes in this data', 'category' => 'alerts'];
                $followUps[] = ['text' => 'Show me the real-time dashboard', 'category' => 'dashboard'];
                break;

            case 'planning':
                $followUps[] = ['text' => 'What are the upcoming milestones?', 'category' => 'milestones'];
                $followUps[] = ['text' => 'Show me the resource requirements', 'category' => 'resources'];
                break;
        }

        return $followUps;
    }

    /**
     * Generate data-driven follow-ups
     */
    private function generateDataDrivenFollowUps($queryResult, $questionAnalysis)
    {
        $followUps = [];

        if (!isset($queryResult['data']) || empty($queryResult['data'])) {
            $followUps[] = ['text' => 'Why might this data be empty?', 'category' => 'investigation'];
            $followUps[] = ['text' => 'Show me related data that might be available', 'category' => 'alternatives'];
            return $followUps;
        }

        $recordCount = count($queryResult['data']);
        $firstRecord = (array) $queryResult['data'][0];

        // Record count based suggestions
        if ($recordCount == 1) {
            $followUps[] = ['text' => 'Show me similar records', 'category' => 'similar'];
            $followUps[] = ['text' => 'What is the history of this record?', 'category' => 'history'];
        } elseif ($recordCount > 20) {
            $followUps[] = ['text' => 'Show me a summary of these results', 'category' => 'summary'];
            $followUps[] = ['text' => 'Filter these results by specific criteria', 'category' => 'filter'];
        }

        // Field-based suggestions
        foreach ($firstRecord as $field => $value) {
            $fieldLower = strtolower($field);
            
            if (strpos($fieldLower, 'status') !== false) {
                $followUps[] = ['text' => 'Break down by status categories', 'category' => 'breakdown'];
                $followUps[] = ['text' => 'Show status change history', 'category' => 'history'];
            }
            
            if (strpos($fieldLower, 'department') !== false) {
                $followUps[] = ['text' => 'Compare across departments', 'category' => 'comparison'];
                $followUps[] = ['text' => 'Show department performance metrics', 'category' => 'metrics'];
            }
            
            if (strpos($fieldLower, 'date') !== false) {
                $followUps[] = ['text' => 'Show timeline view of this data', 'category' => 'timeline'];
                $followUps[] = ['text' => 'Group by time periods', 'category' => 'temporal'];
            }
        }

        return array_slice($followUps, 0, 4); // Limit data-driven suggestions
    }

    /**
     * Generate contextual follow-ups
     */
    private function generateContextualFollowUps($questionAnalysis, $resultAnalysis, $userContext)
    {
        $followUps = [];
        $entities = $questionAnalysis['entities'];

        foreach ($entities as $entity) {
            switch ($entity) {
                case 'employees':
                    $followUps[] = ['text' => 'Show employee performance metrics', 'category' => 'performance'];
                    $followUps[] = ['text' => 'What are the employee skill sets?', 'category' => 'skills'];
                    $followUps[] = ['text' => 'Show employee career progression', 'category' => 'career'];
                    break;

                case 'projects':
                    $followUps[] = ['text' => 'Show project timeline and milestones', 'category' => 'timeline'];
                    $followUps[] = ['text' => 'What are the project risks and issues?', 'category' => 'risks'];
                    $followUps[] = ['text' => 'Show project budget utilization', 'category' => 'budget'];
                    break;

                case 'attendance':
                    $followUps[] = ['text' => 'Show attendance patterns and trends', 'category' => 'patterns'];
                    $followUps[] = ['text' => 'What are the overtime statistics?', 'category' => 'overtime'];
                    $followUps[] = ['text' => 'Show remote work analytics', 'category' => 'remote'];
                    break;

                case 'departments':
                    $followUps[] = ['text' => 'Compare department performance', 'category' => 'comparison'];
                    $followUps[] = ['text' => 'Show department resource allocation', 'category' => 'resources'];
                    $followUps[] = ['text' => 'What are the department goals and KPIs?', 'category' => 'goals'];
                    break;
            }
        }

        return array_slice($followUps, 0, 3); // Limit contextual suggestions
    }

    /**
     * Generate semantic-based follow-ups
     */
    private function generateSemanticBasedFollowUps($semanticAnalysis, $resultAnalysis)
    {
        $followUps = [];

        if (isset($semanticAnalysis['domain_context']['hr_function'])) {
            $hrFunction = $semanticAnalysis['domain_context']['hr_function'];
            
            switch ($hrFunction) {
                case 'recruitment':
                    $followUps[] = ['text' => 'Show hiring pipeline and conversion rates', 'category' => 'pipeline'];
                    $followUps[] = ['text' => 'What are the recruitment costs?', 'category' => 'costs'];
                    break;

                case 'performance':
                    $followUps[] = ['text' => 'Show performance improvement plans', 'category' => 'improvement'];
                    $followUps[] = ['text' => 'What are the top performers doing differently?', 'category' => 'best_practices'];
                    break;

                case 'compensation':
                    $followUps[] = ['text' => 'Show salary benchmarking data', 'category' => 'benchmarking'];
                    $followUps[] = ['text' => 'What are the pay equity metrics?', 'category' => 'equity'];
                    break;
            }
        }

        return $followUps;
    }

    /**
     * Generate smart suggestions based on patterns
     */
    private function generateSmartSuggestions($questionAnalysis, $resultAnalysis)
    {
        $suggestions = [];

        // Time-based smart suggestions
        if ($questionAnalysis['time_context']) {
            switch ($questionAnalysis['time_context']) {
                case 'today':
                    $suggestions[] = ['text' => 'Compare with yesterday\'s data', 'category' => 'comparison'];
                    $suggestions[] = ['text' => 'Show weekly trend including today', 'category' => 'trend'];
                    break;

                case 'this_month':
                    $suggestions[] = ['text' => 'Compare with last month', 'category' => 'comparison'];
                    $suggestions[] = ['text' => 'Show month-over-month growth', 'category' => 'growth'];
                    break;

                case 'this_year':
                    $suggestions[] = ['text' => 'Show year-over-year comparison', 'category' => 'yearly'];
                    $suggestions[] = ['text' => 'What are the quarterly trends?', 'category' => 'quarterly'];
                    break;
            }
        }

        // Result-based smart suggestions
        if ($resultAnalysis['contains_aggregation']) {
            $suggestions[] = ['text' => 'Show the underlying detailed data', 'category' => 'details'];
            $suggestions[] = ['text' => 'Break down by different dimensions', 'category' => 'dimensions'];
        }

        if ($resultAnalysis['contains_status']) {
            $suggestions[] = ['text' => 'Show status transition patterns', 'category' => 'transitions'];
            $suggestions[] = ['text' => 'What causes status changes?', 'category' => 'causation'];
        }

        return $suggestions;
    }

    /**
     * Remove duplicates and rank by relevance
     */
    private function removeDuplicatesAndRank($followUps)
    {
        // Remove duplicates based on text
        $unique = [];
        $seen = [];

        foreach ($followUps as $followUp) {
            $text = $followUp['text'];
            if (!in_array($text, $seen)) {
                $seen[] = $text;
                $unique[] = $followUp;
            }
        }

        // Rank by category priority
        $categoryPriority = [
            'drill_down' => 10,
            'comparison' => 9,
            'trend' => 8,
            'insights' => 7,
            'details' => 6,
            'filter' => 5,
            'related' => 4,
            'history' => 3,
            'planning' => 2,
            'alternatives' => 1
        ];

        usort($unique, function($a, $b) use ($categoryPriority) {
            $priorityA = $categoryPriority[$a['category']] ?? 0;
            $priorityB = $categoryPriority[$b['category']] ?? 0;
            return $priorityB - $priorityA;
        });

        return $unique;
    }

    /**
     * Format follow-up results
     */
    private function formatFollowUpResults($followUps, $limit)
    {
        $formatted = [];
        $count = 0;

        foreach ($followUps as $followUp) {
            if ($count >= $limit) break;
            
            $formatted[] = [
                'text' => $followUp['text'],
                'category' => $followUp['category'],
                'priority' => $this->calculatePriority($followUp),
                'icon' => $this->getIconForCategory($followUp['category'])
            ];
            
            $count++;
        }

        return $formatted;
    }

    /**
     * Calculate priority score for follow-up
     */
    private function calculatePriority($followUp)
    {
        $basePriority = [
            'drill_down' => 'high',
            'comparison' => 'high',
            'trend' => 'medium',
            'insights' => 'medium',
            'details' => 'medium',
            'filter' => 'low',
            'related' => 'low'
        ];

        return $basePriority[$followUp['category']] ?? 'low';
    }

    /**
     * Get icon for category
     */
    private function getIconForCategory($category)
    {
        $icons = [
            'drill_down' => 'fas fa-search-plus',
            'comparison' => 'fas fa-balance-scale',
            'trend' => 'fas fa-chart-line',
            'insights' => 'fas fa-lightbulb',
            'details' => 'fas fa-info-circle',
            'filter' => 'fas fa-filter',
            'related' => 'fas fa-link',
            'history' => 'fas fa-history',
            'planning' => 'fas fa-calendar-alt',
            'alternatives' => 'fas fa-random'
        ];

        return $icons[$category] ?? 'fas fa-question-circle';
    }

    /**
     * Get fallback follow-ups
     */
    private function getFallbackFollowUps($originalQuestion)
    {
        return [
            ['text' => 'Show me related information', 'category' => 'related', 'priority' => 'medium'],
            ['text' => 'Can you provide more details?', 'category' => 'details', 'priority' => 'medium'],
            ['text' => 'What are the recent trends?', 'category' => 'trend', 'priority' => 'medium'],
            ['text' => 'How does this compare to last month?', 'category' => 'comparison', 'priority' => 'low'],
            ['text' => 'Show me a summary of this data', 'category' => 'summary', 'priority' => 'low']
        ];
    }

    /**
     * Initialize question patterns
     */
    private function initializeQuestionPatterns()
    {
        $this->questionPatterns = [
            'count_patterns' => [
                'follow_ups' => ['breakdown', 'comparison', 'trend'],
                'priority' => 'high'
            ],
            'list_patterns' => [
                'follow_ups' => ['filter', 'sort', 'details'],
                'priority' => 'medium'
            ],
            'analytics_patterns' => [
                'follow_ups' => ['insights', 'recommendations', 'benchmark'],
                'priority' => 'high'
            ]
        ];
    }

    /**
     * Initialize contextual rules
     */
    private function initializeContextualRules()
    {
        $this->contextualRules = [
            'employee_context' => [
                'performance', 'skills', 'career', 'attendance'
            ],
            'project_context' => [
                'timeline', 'risks', 'budget', 'resources'
            ],
            'department_context' => [
                'comparison', 'performance', 'goals', 'metrics'
            ]
        ];
    }

    /**
     * Initialize smart suggestions
     */
    private function initializeSmartSuggestions()
    {
        $this->smartSuggestions = [
            'temporal_suggestions' => [
                'today' => ['yesterday_comparison', 'weekly_trend'],
                'this_month' => ['last_month_comparison', 'monthly_growth'],
                'this_year' => ['yearly_comparison', 'quarterly_trends']
            ],
            'data_type_suggestions' => [
                'aggregation' => ['detailed_data', 'breakdown'],
                'status' => ['transitions', 'causation'],
                'performance' => ['improvement', 'benchmarking']
            ]
        ];
    }
}
