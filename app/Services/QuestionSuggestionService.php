<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Question Suggestion Service - Intelligent Question and Follow-up Generation
 * 
 * Features:
 * - Context-aware question suggestions
 * - Dynamic follow-up questions based on results
 * - Role-based question filtering
 * - Category-specific suggestions
 * - Trending and popular questions
 */
class QuestionSuggestionService
{
    private $userId;
    private $userRole;
    private $questionCategories;
    private $followUpPatterns;
    private $contextualSuggestions;

    public function __construct($userId = null, $userRole = null)
    {
        $this->userId = $userId ?: Auth::id();
        $this->userRole = $userRole ?: $this->determineUserRole();
        $this->initializeQuestionDatabase();
    }

    /**
     * Get initial question suggestions for the user
     */
    public function getInitialSuggestions($limit = 8)
    {
        $suggestions = [];

        // Get role-based suggestions
        $roleSuggestions = $this->getRoleBasedSuggestions();
        
        // Get popular questions
        $popularQuestions = $this->getPopularQuestions();
        
        // Get trending questions
        $trendingQuestions = $this->getTrendingQuestions();

        // Combine and prioritize
        $allSuggestions = array_merge($roleSuggestions, $popularQuestions, $trendingQuestions);
        
        // Remove duplicates and limit
        $uniqueSuggestions = array_unique($allSuggestions, SORT_REGULAR);
        
        return array_slice($uniqueSuggestions, 0, $limit);
    }

    /**
     * Generate follow-up questions based on query results
     */
    public function generateFollowUpQuestions($originalQuestion, $queryResult, $semanticAnalysis = null, $limit = 5)
    {
        try {
            $followUps = [];

            // Analyze the original question and results
            $questionType = $this->analyzeQuestionType($originalQuestion);
            $resultContext = $this->analyzeResultContext($queryResult);
            
            // Generate context-specific follow-ups
            $contextFollowUps = $this->generateContextualFollowUps($questionType, $resultContext, $originalQuestion);
            $followUps = array_merge($followUps, $contextFollowUps);

            // Generate semantic follow-ups if analysis available
            if ($semanticAnalysis) {
                $semanticFollowUps = $this->generateSemanticFollowUps($semanticAnalysis, $queryResult);
                $followUps = array_merge($followUps, $semanticFollowUps);
            }

            // Generate data-driven follow-ups
            $dataFollowUps = $this->generateDataDrivenFollowUps($queryResult, $originalQuestion);
            $followUps = array_merge($followUps, $dataFollowUps);

            // Generate related topic follow-ups
            $relatedFollowUps = $this->generateRelatedTopicFollowUps($questionType, $originalQuestion);
            $followUps = array_merge($followUps, $relatedFollowUps);

            // Remove duplicates and filter by role
            $uniqueFollowUps = array_unique($followUps);
            $filteredFollowUps = $this->filterByUserRole($uniqueFollowUps);

            return array_slice($filteredFollowUps, 0, $limit);

        } catch (\Exception $e) {
            Log::error('Error generating follow-up questions: ' . $e->getMessage());
            return $this->getFallbackFollowUps($originalQuestion);
        }
    }

    /**
     * Get suggestions by category
     */
    public function getSuggestionsByCategory($category, $limit = 10)
    {
        $categoryQuestions = $this->questionCategories[$category] ?? [];
        
        // Filter by user role
        $filteredQuestions = $this->filterByUserRole($categoryQuestions);
        
        // Randomize for variety
        shuffle($filteredQuestions);
        
        return array_slice($filteredQuestions, 0, $limit);
    }

    /**
     * Get all available categories
     */
    public function getAvailableCategories()
    {
        $allCategories = array_keys($this->questionCategories);
        
        // Filter categories based on user role
        return $this->filterCategoriesByRole($allCategories);
    }

    /**
     * Search suggestions by keyword
     */
    public function searchSuggestions($keyword, $limit = 10)
    {
        $keyword = strtolower($keyword);
        $matchingSuggestions = [];

        foreach ($this->questionCategories as $category => $questions) {
            foreach ($questions as $question) {
                if (strpos(strtolower($question['text']), $keyword) !== false ||
                    strpos(strtolower($question['description'] ?? ''), $keyword) !== false) {
                    $matchingSuggestions[] = $question;
                }
            }
        }

        // Filter by user role
        $filteredSuggestions = $this->filterByUserRole($matchingSuggestions);
        
        return array_slice($filteredSuggestions, 0, $limit);
    }

    /**
     * Get role-based suggestions
     */
    private function getRoleBasedSuggestions()
    {
        switch ($this->userRole) {
            case 'super_admin':
                return [
                    ['text' => 'Show me overall company statistics', 'category' => 'analytics', 'priority' => 'high'],
                    ['text' => 'What are the salary trends across departments?', 'category' => 'compensation', 'priority' => 'high'],
                    ['text' => 'Show attendance patterns for this month', 'category' => 'attendance', 'priority' => 'medium'],
                    ['text' => 'Which projects are behind schedule?', 'category' => 'projects', 'priority' => 'high'],
                    ['text' => 'Show employee performance metrics', 'category' => 'performance', 'priority' => 'medium']
                ];
                
            case 'manager':
                return [
                    ['text' => 'Show my team\'s attendance this week', 'category' => 'attendance', 'priority' => 'high'],
                    ['text' => 'What projects is my team working on?', 'category' => 'projects', 'priority' => 'high'],
                    ['text' => 'Show pending leave requests for my team', 'category' => 'leaves', 'priority' => 'medium'],
                    ['text' => 'How many employees are in my department?', 'category' => 'employees', 'priority' => 'medium']
                ];
                
            case 'employee':
                return [
                    ['text' => 'Show my attendance for this month', 'category' => 'personal', 'priority' => 'high'],
                    ['text' => 'What are my assigned tasks?', 'category' => 'tasks', 'priority' => 'high'],
                    ['text' => 'Show my leave balance', 'category' => 'leaves', 'priority' => 'medium'],
                    ['text' => 'What projects am I working on?', 'category' => 'projects', 'priority' => 'medium']
                ];
                
            default:
                return [
                    ['text' => 'Show company overview', 'category' => 'general', 'priority' => 'medium'],
                    ['text' => 'How many employees work here?', 'category' => 'employees', 'priority' => 'medium'],
                    ['text' => 'What departments do we have?', 'category' => 'departments', 'priority' => 'medium']
                ];
        }
    }

    /**
     * Get popular questions based on usage
     */
    private function getPopularQuestions()
    {
        return [
            ['text' => 'Show me all employees with their departments', 'category' => 'employees', 'popularity' => 95],
            ['text' => 'How many employees joined this year?', 'category' => 'employees', 'popularity' => 88],
            ['text' => 'Show attendance summary for today', 'category' => 'attendance', 'popularity' => 82],
            ['text' => 'What are the current active projects?', 'category' => 'projects', 'popularity' => 79],
            ['text' => 'Show department-wise employee count', 'category' => 'departments', 'popularity' => 76],
            ['text' => 'Who is on leave today?', 'category' => 'leaves', 'popularity' => 73],
            ['text' => 'Show salary disbursement status', 'category' => 'compensation', 'popularity' => 70],
            ['text' => 'What tasks are due this week?', 'category' => 'tasks', 'popularity' => 67]
        ];
    }

    /**
     * Get trending questions
     */
    private function getTrendingQuestions()
    {
        $currentMonth = date('F');
        $currentYear = date('Y');
        
        return [
            ['text' => "Show hiring statistics for {$currentYear}", 'category' => 'analytics', 'trending' => true],
            ['text' => "Who joined the company in {$currentMonth}?", 'category' => 'employees', 'trending' => true],
            ['text' => "Show {$currentMonth} attendance trends", 'category' => 'attendance', 'trending' => true],
            ['text' => "What projects started this quarter?", 'category' => 'projects', 'trending' => true],
            ['text' => "Show year-to-date performance metrics", 'category' => 'performance', 'trending' => true]
        ];
    }

    /**
     * Analyze question type for follow-up generation
     */
    private function analyzeQuestionType($question)
    {
        $question = strtolower($question);
        
        $patterns = [
            'count' => ['how many', 'count', 'total', 'number of'],
            'list' => ['show', 'list', 'display', 'get', 'find'],
            'comparison' => ['compare', 'vs', 'versus', 'difference', 'between'],
            'trend' => ['trend', 'over time', 'monthly', 'yearly', 'growth'],
            'status' => ['status', 'state', 'current', 'active', 'pending'],
            'performance' => ['performance', 'metrics', 'kpi', 'rating', 'score'],
            'personal' => ['my', 'mine', 'i have', 'i am'],
            'analytics' => ['analyze', 'analysis', 'statistics', 'stats', 'summary']
        ];

        foreach ($patterns as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($question, $keyword) !== false) {
                    return $type;
                }
            }
        }

        return 'general';
    }

    /**
     * Analyze result context for follow-up generation
     */
    private function analyzeResultContext($queryResult)
    {
        $context = [
            'has_data' => false,
            'record_count' => 0,
            'data_type' => 'unknown',
            'contains_numbers' => false,
            'contains_dates' => false,
            'contains_departments' => false,
            'contains_employees' => false
        ];

        if (isset($queryResult['data']) && !empty($queryResult['data'])) {
            $context['has_data'] = true;
            $context['record_count'] = count($queryResult['data']);
            
            // Analyze first record to understand data structure
            $firstRecord = (array) $queryResult['data'][0];
            
            foreach ($firstRecord as $field => $value) {
                if (is_numeric($value)) {
                    $context['contains_numbers'] = true;
                }
                if (strpos($field, 'date') !== false || strpos($field, 'time') !== false) {
                    $context['contains_dates'] = true;
                }
                if (strpos($field, 'department') !== false) {
                    $context['contains_departments'] = true;
                }
                if (strpos($field, 'employee') !== false || strpos($field, 'firstname') !== false) {
                    $context['contains_employees'] = true;
                }
            }
        }

        return $context;
    }

    /**
     * Generate contextual follow-up questions
     */
    private function generateContextualFollowUps($questionType, $resultContext, $originalQuestion)
    {
        $followUps = [];

        switch ($questionType) {
            case 'count':
                if ($resultContext['has_data']) {
                    $followUps[] = 'Show me the detailed breakdown of these numbers';
                    $followUps[] = 'How does this compare to last month?';
                    $followUps[] = 'What are the trends over the past year?';
                    if ($resultContext['contains_departments']) {
                        $followUps[] = 'Break this down by department';
                    }
                }
                break;

            case 'list':
                if ($resultContext['record_count'] > 0) {
                    $followUps[] = 'Show me more details about these records';
                    $followUps[] = 'Filter these results by specific criteria';
                    $followUps[] = 'Sort these results differently';
                    if ($resultContext['contains_employees']) {
                        $followUps[] = 'Show contact information for these employees';
                        $followUps[] = 'What projects are these employees working on?';
                    }
                }
                break;

            case 'analytics':
                $followUps[] = 'Show me the trends for this data';
                $followUps[] = 'Compare this to industry benchmarks';
                $followUps[] = 'What factors might be influencing these results?';
                $followUps[] = 'Generate a summary report of this analysis';
                break;

            case 'personal':
                $followUps[] = 'Show my historical data';
                $followUps[] = 'Compare my metrics to team average';
                $followUps[] = 'What are my upcoming deadlines?';
                $followUps[] = 'Show my performance trends';
                break;
        }

        return $followUps;
    }

    /**
     * Generate semantic follow-ups based on analysis
     */
    private function generateSemanticFollowUps($semanticAnalysis, $queryResult)
    {
        $followUps = [];

        // Based on intent
        if (isset($semanticAnalysis['intent']['primary'])) {
            switch ($semanticAnalysis['intent']['primary']) {
                case 'retrieve':
                    $followUps[] = 'Analyze this data further';
                    $followUps[] = 'Show related information';
                    break;
                case 'count':
                    $followUps[] = 'Show the percentage breakdown';
                    $followUps[] = 'Compare with previous periods';
                    break;
                case 'analyze':
                    $followUps[] = 'What recommendations do you have?';
                    $followUps[] = 'Show the key insights from this analysis';
                    break;
            }
        }

        // Based on entities
        if (isset($semanticAnalysis['entities']['tables'])) {
            foreach ($semanticAnalysis['entities']['tables'] as $table) {
                switch ($table) {
                    case 'employees':
                        $followUps[] = 'Show employee performance metrics';
                        $followUps[] = 'What are the employee satisfaction levels?';
                        break;
                    case 'departments':
                        $followUps[] = 'Compare department performance';
                        $followUps[] = 'Show department budget utilization';
                        break;
                    case 'projects':
                        $followUps[] = 'Show project timeline and milestones';
                        $followUps[] = 'What are the project risk factors?';
                        break;
                }
            }
        }

        return $followUps;
    }

    /**
     * Generate data-driven follow-ups
     */
    private function generateDataDrivenFollowUps($queryResult, $originalQuestion)
    {
        $followUps = [];

        if (!isset($queryResult['data']) || empty($queryResult['data'])) {
            $followUps[] = 'Why might this data be empty?';
            $followUps[] = 'Show related data that might be available';
            return $followUps;
        }

        $recordCount = count($queryResult['data']);
        
        if ($recordCount > 10) {
            $followUps[] = 'Show me the top 10 results only';
            $followUps[] = 'Filter these results by specific criteria';
        }

        if ($recordCount > 1) {
            $followUps[] = 'Sort these results by different criteria';
            $followUps[] = 'Group these results by category';
        }

        // Analyze data patterns
        $firstRecord = (array) $queryResult['data'][0];
        
        if (isset($firstRecord['created_at']) || isset($firstRecord['date'])) {
            $followUps[] = 'Show the timeline view of this data';
            $followUps[] = 'Group this data by month/quarter';
        }

        if (isset($firstRecord['status'])) {
            $followUps[] = 'Break down by status';
            $followUps[] = 'Show status change history';
        }

        return $followUps;
    }

    /**
     * Generate related topic follow-ups
     */
    private function generateRelatedTopicFollowUps($questionType, $originalQuestion)
    {
        $relatedTopics = [
            'employees' => [
                'Show department distribution',
                'What are the recent hires?',
                'Show employee skill matrix',
                'What are the upcoming birthdays?'
            ],
            'attendance' => [
                'Show leave patterns',
                'What are the overtime trends?',
                'Show remote work statistics',
                'Who has perfect attendance?'
            ],
            'projects' => [
                'Show project budgets',
                'What are the project dependencies?',
                'Show team allocations',
                'What are the project risks?'
            ],
            'compensation' => [
                'Show salary bands by role',
                'What are the bonus distributions?',
                'Show cost center analysis',
                'Compare with market rates'
            ]
        ];

        // Determine topic from original question
        $question = strtolower($originalQuestion);
        foreach ($relatedTopics as $topic => $suggestions) {
            if (strpos($question, $topic) !== false || strpos($question, rtrim($topic, 's')) !== false) {
                return array_slice($suggestions, 0, 3);
            }
        }

        return [];
    }

    /**
     * Filter suggestions by user role
     */
    private function filterByUserRole($suggestions)
    {
        return array_filter($suggestions, function($suggestion) {
            $text = is_array($suggestion) ? $suggestion['text'] : $suggestion;
            $text = strtolower($text);

            // Super admin can see everything
            if ($this->userRole === 'super_admin') {
                return true;
            }

            // Filter sensitive queries for non-admin users
            $sensitiveKeywords = ['salary', 'compensation', 'budget', 'cost', 'revenue'];
            foreach ($sensitiveKeywords as $keyword) {
                if (strpos($text, $keyword) !== false && $this->userRole !== 'super_admin') {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Filter categories by user role
     */
    private function filterCategoriesByRole($categories)
    {
        if ($this->userRole === 'super_admin') {
            return $categories;
        }

        // Remove sensitive categories for non-admin users
        $restrictedCategories = ['compensation', 'analytics', 'admin'];
        
        return array_filter($categories, function($category) use ($restrictedCategories) {
            return !in_array($category, $restrictedCategories);
        });
    }

    /**
     * Get fallback follow-ups when generation fails
     */
    private function getFallbackFollowUps($originalQuestion)
    {
        return [
            'Show me related information',
            'Can you provide more details?',
            'What are the recent trends?',
            'How does this compare to last month?',
            'Show me a summary of this data'
        ];
    }

    /**
     * Determine user role
     */
    private function determineUserRole()
    {
        $user = Auth::user();
        if (!$user) return 'guest';

        if ($user->role_users_id == 1) {
            return 'super_admin';
        }

        // Add more role logic as needed
        return 'employee';
    }

    /**
     * Initialize question database
     */
    private function initializeQuestionDatabase()
    {
        $this->questionCategories = [
            'employees' => [
                ['text' => 'Show me all employees with their departments', 'description' => 'Complete employee directory'],
                ['text' => 'How many employees work in each department?', 'description' => 'Department-wise headcount'],
                ['text' => 'Who are the newest employees?', 'description' => 'Recent hires'],
                ['text' => 'Show employees by designation', 'description' => 'Role-based employee list'],
                ['text' => 'Which employees have birthdays this month?', 'description' => 'Birthday calendar'],
                ['text' => 'Show employee contact information', 'description' => 'Contact directory'],
                ['text' => 'Who are the long-serving employees?', 'description' => 'Employee tenure analysis'],
                ['text' => 'Show employees by location', 'description' => 'Geographic distribution']
            ],
            'attendance' => [
                ['text' => 'Show today\'s attendance', 'description' => 'Daily attendance report'],
                ['text' => 'Who is absent today?', 'description' => 'Today\'s absentees'],
                ['text' => 'Show attendance trends for this month', 'description' => 'Monthly attendance analysis'],
                ['text' => 'Which employees have perfect attendance?', 'description' => 'Perfect attendance records'],
                ['text' => 'Show late arrivals this week', 'description' => 'Punctuality report'],
                ['text' => 'What are the overtime hours this month?', 'description' => 'Overtime analysis'],
                ['text' => 'Show remote work statistics', 'description' => 'Work from home data'],
                ['text' => 'Who worked on weekends?', 'description' => 'Weekend work report']
            ],
            'projects' => [
                ['text' => 'Show all active projects', 'description' => 'Current project portfolio'],
                ['text' => 'Which projects are behind schedule?', 'description' => 'Project delays report'],
                ['text' => 'Show project team assignments', 'description' => 'Team allocation matrix'],
                ['text' => 'What projects are completing this month?', 'description' => 'Project completion forecast'],
                ['text' => 'Show project budgets and spending', 'description' => 'Financial project overview'],
                ['text' => 'Which projects need more resources?', 'description' => 'Resource requirement analysis'],
                ['text' => 'Show client project distribution', 'description' => 'Client portfolio view'],
                ['text' => 'What are the project milestones?', 'description' => 'Milestone tracking']
            ],
            'tasks' => [
                ['text' => 'Show all pending tasks', 'description' => 'Task backlog overview'],
                ['text' => 'What tasks are due this week?', 'description' => 'Weekly task deadlines'],
                ['text' => 'Show overdue tasks', 'description' => 'Overdue task report'],
                ['text' => 'Which tasks are in progress?', 'description' => 'Active task status'],
                ['text' => 'Show task completion rates', 'description' => 'Productivity metrics'],
                ['text' => 'What are the high-priority tasks?', 'description' => 'Priority task list'],
                ['text' => 'Show task assignments by employee', 'description' => 'Workload distribution'],
                ['text' => 'Which tasks are blocked?', 'description' => 'Blocked task analysis']
            ],
            'leaves' => [
                ['text' => 'Who is on leave today?', 'description' => 'Current leave status'],
                ['text' => 'Show pending leave requests', 'description' => 'Leave approval queue'],
                ['text' => 'What are the leave balances?', 'description' => 'Leave entitlement report'],
                ['text' => 'Show leave patterns by department', 'description' => 'Department leave analysis'],
                ['text' => 'Which employees have used all their leave?', 'description' => 'Leave exhaustion report'],
                ['text' => 'Show upcoming planned leaves', 'description' => 'Leave calendar'],
                ['text' => 'What are the leave trends this year?', 'description' => 'Annual leave analysis'],
                ['text' => 'Show sick leave statistics', 'description' => 'Health-related absence data']
            ],
            'departments' => [
                ['text' => 'Show all departments', 'description' => 'Department directory'],
                ['text' => 'Which department has the most employees?', 'description' => 'Department size comparison'],
                ['text' => 'Show department performance metrics', 'description' => 'Department KPIs'],
                ['text' => 'What are the department budgets?', 'description' => 'Financial allocation by department'],
                ['text' => 'Show department project allocations', 'description' => 'Project distribution'],
                ['text' => 'Which departments are hiring?', 'description' => 'Recruitment activity'],
                ['text' => 'Show department attendance rates', 'description' => 'Department attendance comparison'],
                ['text' => 'What are the department goals?', 'description' => 'Strategic objectives']
            ],
            'compensation' => [
                ['text' => 'Show salary disbursement status', 'description' => 'Payroll processing status'],
                ['text' => 'What are the salary trends?', 'description' => 'Compensation analysis'],
                ['text' => 'Show bonus distributions', 'description' => 'Incentive payments'],
                ['text' => 'Which employees received raises?', 'description' => 'Salary increment report'],
                ['text' => 'Show cost center analysis', 'description' => 'Financial cost breakdown'],
                ['text' => 'What are the salary bands by role?', 'description' => 'Compensation structure'],
                ['text' => 'Show overtime compensation', 'description' => 'Additional pay analysis'],
                ['text' => 'Compare salaries across departments', 'description' => 'Inter-department pay comparison']
            ],
            'analytics' => [
                ['text' => 'Show company dashboard', 'description' => 'Executive summary view'],
                ['text' => 'What are the key HR metrics?', 'description' => 'HR KPI dashboard'],
                ['text' => 'Show hiring statistics', 'description' => 'Recruitment analytics'],
                ['text' => 'What are the retention rates?', 'description' => 'Employee retention analysis'],
                ['text' => 'Show productivity metrics', 'description' => 'Performance indicators'],
                ['text' => 'What are the cost per employee metrics?', 'description' => 'HR cost analysis'],
                ['text' => 'Show diversity and inclusion stats', 'description' => 'D&I metrics'],
                ['text' => 'What are the training completion rates?', 'description' => 'Learning and development metrics']
            ],
            'personal' => [
                ['text' => 'Show my attendance record', 'description' => 'Personal attendance history'],
                ['text' => 'What are my assigned tasks?', 'description' => 'Personal task list'],
                ['text' => 'Show my leave balance', 'description' => 'Personal leave entitlement'],
                ['text' => 'What projects am I working on?', 'description' => 'Personal project assignments'],
                ['text' => 'Show my performance metrics', 'description' => 'Personal KPIs'],
                ['text' => 'What are my upcoming deadlines?', 'description' => 'Personal deadline calendar'],
                ['text' => 'Show my team members', 'description' => 'Team directory'],
                ['text' => 'What training do I need to complete?', 'description' => 'Personal learning plan']
            ]
        ];
    }
}
