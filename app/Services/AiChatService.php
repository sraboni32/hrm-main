<?php

namespace App\Services;

use App\Models\User;
use App\Models\Employee;
use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Services\AiDatabaseService;
use App\Services\AiAgentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiChatService
{
    private $apiKey;
    private $apiUrl;
    private $model;
    private $maxTokens;
    private $temperature;
    private $topP;
    private $frequencyPenalty;
    private $presencePenalty;
    private $databaseService;

    public function __construct()
    {
        $this->databaseService = new AiDatabaseService();
        // Use NVIDIA API by default
        $this->apiKey = config('services.nvidia_ai.api_key');
        $baseUrl = config('services.nvidia_ai.base_url', 'https://integrate.api.nvidia.com/v1');
        $this->apiUrl = rtrim($baseUrl, '/') . '/chat/completions';
        $this->model = config('services.nvidia_ai.model', 'nvidia/llama-3.3-nemotron-super-49b-v1');
        $this->maxTokens = (int) config('services.nvidia_ai.max_tokens', 4096);
        $this->temperature = (float) config('services.nvidia_ai.temperature', 0.6);
        $this->topP = (float) config('services.nvidia_ai.top_p', 0.95);
        $this->frequencyPenalty = (float) config('services.nvidia_ai.frequency_penalty', 0);
        $this->presencePenalty = (float) config('services.nvidia_ai.presence_penalty', 0);

        // Log configuration for debugging
        Log::info('AI Chat Service initialized', [
            'api_url' => $this->apiUrl,
            'model' => $this->model,
            'has_api_key' => !empty($this->apiKey),
            'api_key_prefix' => $this->apiKey ? substr($this->apiKey, 0, 10) . '...' : 'MISSING'
        ]);
    }

    /**
     * Process user message and get AI response
     */
    public function processMessage($userId, $message, $conversationId = null)
    {
        try {
            // Get or create conversation
            $conversation = $this->getOrCreateConversation($userId, $conversationId);

            // Save user message
            $userMessage = $this->saveMessage($conversation->id, 'user', $message);

            // Get user context
            $userContext = $this->getUserContext($userId);

            // Use AI Agent to query database dynamically
            $aiAgent = new AiAgentService($userId);
            $agentResult = $aiAgent->executeQuery($message);

            // Prepare messages for AI with agent data
            $messages = $this->prepareMessages($conversation, $userContext, $message, $agentResult);

            // Add SQL query information for super admin (internal context only)
            if (isset($agentResult['sql_query']) && $userContext['role'] === 'super_admin') {
                $messages[] = [
                    'role' => 'system',
                    'content' => "INTERNAL: Query executed successfully. Type: {$agentResult['query_type']}. Result count: " . ($agentResult['data']['count'] ?? 'N/A') . ". Tables: " . implode(', ', $agentResult['schema_used'] ?? []) . ". IMPORTANT: Do not show SQL code in your response to the user. Present the data in a natural, user-friendly way."
                ];
            }

            // Add database schema context for super admin
            if ($userContext['role'] === 'super_admin') {
                $messages[] = [
                    'role' => 'system',
                    'content' => "You have access to comprehensive database information including employees, departments, projects, tasks, attendance, leaves, salary data, and more. Present all information in a natural, conversational way without showing technical SQL details to the user."
                ];
            }

            // Add instruction to hide technical details
            $messages[] = [
                'role' => 'system',
                'content' => "CRITICAL: Never show SQL queries, technical error messages, or database schema details in your response. Always present information in a natural, user-friendly manner. If there were any technical issues, simply provide the best available information without mentioning the technical problems."
            ];

            // Get AI response
            $aiResponse = $this->callOpenAI($messages);

            // Save AI response
            $assistantMessage = $this->saveMessage($conversation->id, 'assistant', $aiResponse['content'], $aiResponse['metadata']);

            // Generate recommended questions based on the query and user role
            $recommendedQuestions = $this->generateRecommendedQuestions($message, $userContext, $agentResult);

            return [
                'success' => true,
                'conversation_id' => $conversation->id,
                'message' => $aiResponse['content'],
                'recommended_questions' => $recommendedQuestions,
                'metadata' => $aiResponse['metadata']
            ];

        } catch (\Exception $e) {
            Log::error('AI Chat Service Error: ' . $e->getMessage(), [
                'user_id' => $userId,
                'conversation_id' => $conversationId,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'I apologize, but I\'m experiencing technical difficulties. Please try again later or contact your HR department for immediate assistance.',
                'conversation_id' => $conversationId
            ];
        }
    }

    /**
     * Get or create conversation
     */
    private function getOrCreateConversation($userId, $conversationId = null)
    {
        if ($conversationId) {
            $conversation = AiChatConversation::where('id', $conversationId)
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();

            if ($conversation) {
                return $conversation;
            }
        }

        return AiChatConversation::create([
            'user_id' => $userId,
            'session_id' => Str::uuid(),
            'title' => 'HR Chat Session',
            'is_active' => true
        ]);
    }

    /**
     * Save message to database
     */
    private function saveMessage($conversationId, $type, $message, $metadata = null)
    {
        return AiChatMessage::create([
            'conversation_id' => $conversationId,
            'type' => $type,
            'message' => $message,
            'metadata' => $metadata
        ]);
    }

    /**
     * Get user context for personalized responses
     */
    private function getUserContext($userId)
    {
        $user = User::with(['employee.department', 'employee.designation'])->find($userId);

        if (!$user || !$user->employee) {
            // Handle users without employee records (like super admin)
            return [
                'user_id' => $userId,
                'role' => $user && $user->role_users_id == 1 ? 'super_admin' : 'user',
                'name' => $user->username ?? 'User',
                'department' => null,
                'designation' => null,
                'joining_date' => null,
                'employee_id' => null
            ];
        }

        $employee = $user->employee;

        return [
            'user_id' => $userId,
            'role' => $user->role_users_id == 1 ? 'admin' : 'employee',
            'name' => trim($employee->firstname . ' ' . $employee->lastname),
            'department' => $employee->department->department_name ?? null,
            'designation' => $employee->designation->designation ?? null,
            'joining_date' => $employee->joining_date ?? null,
            'employee_id' => $employee->id ?? null
        ];
    }

    /**
     * Prepare messages for OpenAI API with AI Agent data
     */
    private function prepareMessages($conversation, $userContext, $currentMessage, $agentResult = null)
    {
        // System prompt with HR context and agent data
        $systemPrompt = $this->buildSystemPrompt($userContext, $agentResult);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Add conversation history (last 10 messages for context)
        $contextMessages = $conversation->getContextMessages(8);
        $messages = array_merge($messages, $contextMessages);

        // Add current message if not already in context
        $lastMessage = end($contextMessages);
        if (!$lastMessage || $lastMessage['content'] !== $currentMessage) {
            $messages[] = ['role' => 'user', 'content' => $currentMessage];
        }

        return $messages;
    }

    /**
     * Build system prompt optimized for Llama 3.3 70B with AI Agent data
     */
    private function buildSystemPrompt($userContext, $agentResult = null)
    {
        $companyName = config('app.name', 'Company');

        $prompt = "<|begin_of_text|><|start_header_id|>system<|end_header_id|>\n\n";
        $prompt .= "You are an intelligent HR Assistant for {$companyName} with real-time database access.\n\n";
        $prompt .= "You can query the database dynamically to provide accurate, up-to-date information.\n\n";

        $prompt .= "USER PROFILE:\n";
        $prompt .= "• Name: {$userContext['name']}\n";
        $prompt .= "• Role: " . ucfirst(str_replace('_', ' ', $userContext['role'])) . "\n";

        if (!empty($userContext['department'])) {
            $prompt .= "• Department: {$userContext['department']}\n";
        }

        if (!empty($userContext['designation'])) {
            $prompt .= "• Position: {$userContext['designation']}\n";
        }

        if (!empty($userContext['joining_date'])) {
            $prompt .= "• Joined: {$userContext['joining_date']}\n";
        }

        // Special handling for super admin
        if ($userContext['role'] === 'super_admin') {
            $prompt .= "• Access Level: Full system administrator\n";
        }

        $prompt .= "\nEXPERTISE AREAS:\n";
        $prompt .= "• Leave policies, vacation requests, and time-off procedures\n";
        $prompt .= "• Attendance tracking, schedules, and work hours\n";
        $prompt .= "• Company policies, procedures, and guidelines\n";
        $prompt .= "• Benefits, compensation, and employee perks\n";
        $prompt .= "• Performance reviews and career development\n";
        $prompt .= "• Workplace procedures and administrative tasks\n";
        $prompt .= "• Task management and project coordination\n";

        // Additional expertise for super admin
        if ($userContext['role'] === 'super_admin') {
            $prompt .= "• System administration and user management\n";
            $prompt .= "• HR analytics and reporting\n";
            $prompt .= "• Policy creation and implementation\n";
            $prompt .= "• Compliance and regulatory guidance\n";
        }

        $prompt .= "\n";

        $prompt .= "RESPONSE GUIDELINES:\n";
        $prompt .= "• Maintain a professional yet friendly tone\n";
        $prompt .= "• Provide clear, actionable information\n";
        $prompt .= "• Use the employee's name naturally in conversation\n";
        $prompt .= "• For company-specific policies, suggest contacting HR directly\n";
        $prompt .= "• Keep responses concise but thorough\n";
        $prompt .= "• For sensitive issues, recommend in-person HR consultation\n";
        $prompt .= "• Always prioritize employee privacy and confidentiality\n\n";

        // Add real-time agent data
        if ($agentResult && $agentResult['success']) {
            $prompt .= "\nREAL-TIME DATABASE QUERY RESULT:\n";
            $prompt .= "CRITICAL: Use these EXACT numbers and data in your response. These are live database values.\n\n";

            $prompt .= "Query Type: " . ($agentResult['query_type'] ?? 'general') . "\n";
            $prompt .= "User Role: " . ($agentResult['user_role'] ?? 'unknown') . "\n\n";

            $prompt .= "DATABASE DATA:\n";
            $prompt .= json_encode($agentResult['data'], JSON_PRETTY_PRINT) . "\n\n";

            $prompt .= "RESPONSE INSTRUCTIONS:\n";
            $prompt .= "• Use the EXACT numbers from the database data above\n";
            $prompt .= "• Reference specific names, counts, and values from the data\n";
            $prompt .= "• Do NOT use placeholder values like '[X]' or 'several'\n";
            $prompt .= "• If a number is 0, say 0 - don't make up numbers\n";
            $prompt .= "• Be specific and accurate with the provided data\n";
            $prompt .= "• If data is missing, clearly state what information is not available\n\n";

        } elseif ($agentResult && !$agentResult['success']) {
            $prompt .= "\nDATABASE QUERY ERROR:\n";
            $prompt .= "The database query failed. Provide general guidance and suggest contacting HR for specific data.\n\n";
        }

        $prompt .= "Remember: You have real-time database access. Always use the actual data provided above in your responses.<|eot_id|>\n\n";

        return $prompt;
    }

    /**
     * Build Super Admin context
     */
    private function buildSuperAdminContext($data)
    {
        $context = "SUPER ADMIN DATA ACCESS:\n";
        $context .= "=========================\n";

        if (isset($data['company_stats'])) {
            $stats = $data['company_stats'];
            $context .= "COMPANY STATISTICS:\n";
            $context .= "• Total Employees: {$stats['total_employees']} employees\n";
            $context .= "• Active Employees: {$stats['active_employees']} currently active\n";
            $context .= "• Total Departments: {$stats['departments_count']} departments\n";
            $context .= "• Total Projects: {$stats['projects_count']} projects\n\n";
        }

        if (isset($data['attendance_stats'])) {
            $attendance = $data['attendance_stats'];
            $context .= "TODAY'S ATTENDANCE:\n";
            $context .= "• Present Today: {$attendance['present_today']} employees\n";
            $context .= "• Absent Today: {$attendance['absent_today']} employees\n";
            if (isset($attendance['late_arrivals'])) {
                $context .= "• Late Arrivals: {$attendance['late_arrivals']} employees\n";
            }
            $context .= "\n";
        }

        if (isset($data['leave_stats'])) {
            $leave = $data['leave_stats'];
            $context .= "LEAVE MANAGEMENT:\n";
            $context .= "• Pending Leave Requests: {$leave['pending_requests']} requests\n";
            $context .= "• Employees on Leave Today: {$leave['on_leave_today']} employees\n";
            if (isset($leave['approved_this_month'])) {
                $context .= "• Approved This Month: {$leave['approved_this_month']} requests\n";
            }
            $context .= "\n";
        }

        if (isset($data['project_stats'])) {
            $projects = $data['project_stats'];
            $context .= "PROJECT STATUS:\n";
            $context .= "• Total Projects: {$projects['total']} projects\n";
            $context .= "• Active Projects: {$projects['active']} in progress\n";
            $context .= "• Completed Projects: {$projects['completed']} completed\n";
            if (isset($projects['overdue'])) {
                $context .= "• Overdue Projects: {$projects['overdue']} overdue\n";
            }
            $context .= "\n";
        }

        if (isset($data['departments']) && is_array($data['departments'])) {
            $context .= "DEPARTMENT DETAILS:\n";
            foreach ($data['departments'] as $dept) {
                $context .= "• {$dept['name']}: {$dept['employee_count']} employees";
                if (isset($dept['head'])) {
                    $context .= " (Head: {$dept['head']})";
                }
                $context .= "\n";
            }
            $context .= "\n";
        }

        return $context;
    }

    /**
     * Build Admin context
     */
    private function buildAdminContext($data)
    {
        $context = "• Access Level: Company/Department level\n";

        if (isset($data['department_info'])) {
            $dept = $data['department_info'];
            $context .= "• Your Department: {$dept['name']}\n";
            $context .= "• Department Head: {$dept['head']}\n";
            $context .= "• Team Size: {$dept['employee_count']}\n";
        }

        if (isset($data['company_stats'])) {
            $stats = $data['company_stats'];
            $context .= "• Company Employees: {$stats['total_employees']}\n";
            $context .= "• Company Projects: {$stats['projects_count']}\n";
        }

        return $context;
    }

    /**
     * Build Employee context
     */
    private function buildEmployeeContext($data)
    {
        $context = "EMPLOYEE PERSONAL DATA:\n";
        $context .= "=======================\n";

        if (isset($data['employee_info'])) {
            $emp = $data['employee_info'];
            $context .= "PERSONAL INFORMATION:\n";
            $context .= "• Full Name: {$emp['name']}\n";
            $context .= "• Department: {$emp['department']}\n";
            $context .= "• Designation: {$emp['designation']}\n";
            $context .= "• Employee ID: {$emp['id']}\n";
            $context .= "• Email: {$emp['email']}\n";
            if (isset($emp['joining_date'])) {
                $context .= "• Joining Date: {$emp['joining_date']}\n";
            }
            if (isset($emp['basic_salary'])) {
                $context .= "• Basic Salary: {$emp['basic_salary']}\n";
            }
            $context .= "\n";

            $context .= "LEAVE INFORMATION:\n";
            $context .= "• Remaining Leave Days: {$emp['remaining_leave']} days\n";
            $context .= "• Total Annual Leave: {$emp['total_leave']} days\n";
            $context .= "• Used Leave Days: " . ($emp['total_leave'] - $emp['remaining_leave']) . " days\n\n";
        }

        if (isset($data['personal_stats'])) {
            $stats = $data['personal_stats'];
            $context .= "WORK STATISTICS:\n";
            $context .= "• Total Projects Assigned: {$stats['total_projects']} projects\n";
            $context .= "• Total Tasks: {$stats['total_tasks']} tasks\n";
            $context .= "• Completed Tasks: {$stats['completed_tasks']} tasks\n";
            $context .= "• Pending Tasks: " . ($stats['total_tasks'] - $stats['completed_tasks']) . " tasks\n";
            $context .= "• Attendance This Month: {$stats['attendance_this_month']} days\n";
            if (isset($stats['leaves_taken_this_year'])) {
                $context .= "• Leaves Taken This Year: {$stats['leaves_taken_this_year']} days\n";
            }
            $context .= "\n";
        }

        if (isset($data['my_projects']) && is_array($data['my_projects'])) {
            $context .= "CURRENT PROJECTS:\n";
            foreach ($data['my_projects'] as $project) {
                $context .= "• {$project['title']} (Status: {$project['status']}, Progress: {$project['progress']}%)\n";
            }
            $context .= "\n";
        }

        if (isset($data['my_tasks']) && is_array($data['my_tasks'])) {
            $context .= "RECENT TASKS:\n";
            $recentTasks = array_slice($data['my_tasks'], 0, 5); // Show only first 5 tasks
            foreach ($recentTasks as $task) {
                $context .= "• {$task['title']} (Status: {$task['status']}, Priority: {$task['priority']})\n";
            }
            $context .= "\n";
        }

        if (isset($data['department_info'])) {
            $dept = $data['department_info'];
            $context .= "DEPARTMENT INFORMATION:\n";
            $context .= "• Department: {$dept['name']}\n";
            if (isset($dept['head'])) {
                $context .= "• Department Head: {$dept['head']}\n";
            }
            $context .= "• Department Size: {$dept['employee_count']} employees\n\n";
        }

        return $context;
    }

    /**
     * Build Client context
     */
    private function buildClientContext($data)
    {
        $context = "• Access Level: Project data only\n";

        if (isset($data['project_stats'])) {
            $stats = $data['project_stats'];
            $context .= "• Your Projects: {$stats['total_projects']}\n";
            $context .= "• Active Projects: {$stats['active_projects']}\n";
            $context .= "• Completed Projects: {$stats['completed_projects']}\n";
        }

        return $context;
    }

    /**
     * Generate recommended questions based on user query and role
     */
    private function generateRecommendedQuestions($userMessage, $userContext, $agentResult = null)
    {
        $role = $userContext['role'];
        $queryType = $agentResult['query_type'] ?? 'general';
        $message = strtolower($userMessage);

        $recommendations = [];

        // Base questions for all users
        $baseQuestions = [
            "What are the company policies?",
            "How do I request time off?",
            "What are my benefits?",
            "Who should I contact for HR issues?"
        ];

        // Role-specific questions
        if ($role === 'super_admin') {
            $recommendations = $this->getSuperAdminRecommendations($queryType, $message);
        } elseif ($role === 'admin') {
            $recommendations = $this->getAdminRecommendations($queryType, $message);
        } elseif ($role === 'employee') {
            $recommendations = $this->getEmployeeRecommendations($queryType, $message);
        } else {
            $recommendations = $baseQuestions;
        }

        // Ensure we have at least 4 recommendations
        if (count($recommendations) < 4) {
            $recommendations = array_merge($recommendations, array_slice($baseQuestions, 0, 4 - count($recommendations)));
        }

        // Return only first 6 recommendations to avoid UI clutter
        return array_slice(array_unique($recommendations), 0, 6);
    }

    /**
     * Get super admin specific recommendations
     */
    private function getSuperAdminRecommendations($queryType, $message)
    {
        $recommendations = [];

        switch ($queryType) {
            case 'employee_count':
            case 'full_employees':
                $recommendations = [
                    "Show me all employees with their departments",
                    "List employees by department",
                    "Show me recent employee joinings",
                    "What's the employee turnover rate?",
                    "Show me employee salary breakdown",
                    "List inactive employees"
                ];
                break;

            case 'attendance':
            case 'full_attendance':
            case 'today_attendance':
                $recommendations = [
                    "Show me today's attendance with department breakdown",
                    "List recent attendance records with departments",
                    "What's the monthly attendance rate?",
                    "Show me late arrivals this week",
                    "Which departments have best attendance?",
                    "Show me attendance trends"
                ];
                break;

            case 'project':
            case 'full_projects':
                $recommendations = [
                    "Show me all projects with client details",
                    "List overdue projects",
                    "What's the project completion rate?",
                    "Show me projects by status",
                    "List projects by department",
                    "Show me project budgets and costs"
                ];
                break;

            case 'task':
            case 'full_tasks':
                $recommendations = [
                    "Show me all tasks with assignments",
                    "List overdue tasks",
                    "What's the task completion rate?",
                    "Show me tasks by priority",
                    "List unassigned tasks",
                    "Show me task distribution by employee"
                ];
                break;

            case 'leave':
            case 'full_leaves':
                $recommendations = [
                    "Show me all leave requests with details",
                    "List pending leave requests",
                    "What's the leave approval rate?",
                    "Show me employees on leave today",
                    "List leave requests by department",
                    "Show me leave usage statistics"
                ];
                break;

            case 'department':
            case 'full_departments':
                $recommendations = [
                    "Show me all departments with employee counts",
                    "List departments without heads",
                    "What's the largest department?",
                    "Show me department performance metrics",
                    "List department budgets",
                    "Show me cross-department projects"
                ];
                break;

            case 'system_overview':
                $recommendations = [
                    "Show me detailed system analytics",
                    "What are the key performance indicators?",
                    "Show me monthly trends",
                    "List system alerts and issues",
                    "Show me user activity statistics",
                    "What needs immediate attention?"
                ];
                break;

            default:
                $recommendations = [
                    "Give me system overview",
                    "Show me today's attendance summary",
                    "List all employees with departments",
                    "Show me project status report",
                    "What are pending leave requests?",
                    "Show me department statistics"
                ];
        }

        return $recommendations;
    }

    /**
     * Get admin specific recommendations
     */
    private function getAdminRecommendations($queryType, $message)
    {
        $recommendations = [];

        switch ($queryType) {
            case 'employee_count':
                $recommendations = [
                    "Show me my team members",
                    "List employees in my department",
                    "What's my department size?",
                    "Show me team performance",
                    "List new team members",
                    "Show me team attendance"
                ];
                break;

            case 'attendance':
                $recommendations = [
                    "Show me my department's attendance today",
                    "List team attendance this week",
                    "Who's absent in my team?",
                    "Show me team attendance trends",
                    "List late arrivals in my department",
                    "What's my team's attendance rate?"
                ];
                break;

            case 'project':
                $recommendations = [
                    "Show me my department's projects",
                    "List team project assignments",
                    "What projects is my team working on?",
                    "Show me project deadlines",
                    "List completed team projects",
                    "Show me project progress"
                ];
                break;

            case 'leave':
                $recommendations = [
                    "Show me team leave requests",
                    "List pending leave approvals",
                    "Who's on leave in my team?",
                    "Show me team leave balances",
                    "List upcoming team leaves",
                    "What's my team's leave usage?"
                ];
                break;

            default:
                $recommendations = [
                    "Show me my team overview",
                    "List my department employees",
                    "What's my team's attendance today?",
                    "Show me team project status",
                    "List team leave requests",
                    "Show me department statistics"
                ];
        }

        return $recommendations;
    }

    /**
     * Get employee specific recommendations
     */
    private function getEmployeeRecommendations($queryType, $message)
    {
        $recommendations = [];

        switch ($queryType) {
            case 'personal':
                $recommendations = [
                    "Show me my leave balance",
                    "What are my assigned tasks?",
                    "Show me my attendance this month",
                    "What projects am I working on?",
                    "Show me my performance metrics",
                    "What's my salary information?"
                ];
                break;

            case 'attendance':
                $recommendations = [
                    "Show me my attendance history",
                    "What's my attendance rate?",
                    "Show me my clock in/out times",
                    "How many days did I work this month?",
                    "Show me my overtime hours",
                    "What's my average work hours?"
                ];
                break;

            case 'leave':
                $recommendations = [
                    "How many leave days do I have left?",
                    "Show me my leave history",
                    "How do I request time off?",
                    "What types of leave can I take?",
                    "Show me my pending leave requests",
                    "When can I take my next vacation?"
                ];
                break;

            case 'task':
                $recommendations = [
                    "What tasks are assigned to me?",
                    "Show me my completed tasks",
                    "What are my pending tasks?",
                    "Show me my task deadlines",
                    "What's my task completion rate?",
                    "Show me my recent task activity"
                ];
                break;

            default:
                $recommendations = [
                    "Show me my personal dashboard",
                    "What's my leave balance?",
                    "Show me my assigned tasks",
                    "What's my attendance this month?",
                    "Show me my projects",
                    "How do I request time off?"
                ];
        }

        return $recommendations;
    }

    /**
     * Call NVIDIA API (Llama 3.3 70B)
     */
    private function callOpenAI($messages)
    {
        try {
            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'top_p' => $this->topP,
                'frequency_penalty' => $this->frequencyPenalty,
                'presence_penalty' => $this->presencePenalty,
                'stream' => false
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(60)->post($this->apiUrl, $payload);

            if (!$response->successful()) {
                Log::error('NVIDIA API Error: ' . $response->status() . ' - ' . $response->body());
                throw new \Exception('NVIDIA API request failed: Status ' . $response->status() . ' - ' . $response->body());
            }

            $data = $response->json();

            // Handle NVIDIA API response format
            $content = '';
            if (isset($data['choices'][0]['message']['content'])) {
                $content = $data['choices'][0]['message']['content'];
            } elseif (isset($data['choices'][0]['text'])) {
                $content = $data['choices'][0]['text'];
            } else {
                $content = 'I apologize, but I couldn\'t generate a response. Please try again.';
            }

            return [
                'content' => trim($content),
                'metadata' => [
                    'model' => $this->model,
                    'provider' => 'nvidia',
                    'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                    'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                    'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                    'response_time' => now()->toISOString()
                ]
            ];

        } catch (\Exception $e) {
            Log::error('NVIDIA API Exception: ' . $e->getMessage());

            // Fallback to OpenAI if NVIDIA fails
            return $this->fallbackToOpenAI($messages);
        }
    }

    /**
     * Fallback to OpenAI if NVIDIA API fails
     */
    private function fallbackToOpenAI($messages)
    {
        try {
            $openaiKey = config('services.openai.api_key');
            if (!$openaiKey) {
                throw new \Exception('No fallback API available');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $openaiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => $messages,
                'max_tokens' => config('services.openai.max_tokens', 500),
                'temperature' => config('services.openai.temperature', 0.7),
            ]);

            if (!$response->successful()) {
                throw new \Exception('Fallback API also failed');
            }

            $data = $response->json();

            return [
                'content' => $data['choices'][0]['message']['content'] ?? 'I apologize, but I\'m experiencing technical difficulties.',
                'metadata' => [
                    'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                    'provider' => 'openai_fallback',
                    'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                    'response_time' => now()->toISOString()
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Fallback API Exception: ' . $e->getMessage());

            return [
                'content' => 'I apologize, but I\'m currently experiencing technical difficulties. Please try again later or contact your HR department for immediate assistance.',
                'metadata' => [
                    'model' => 'error_fallback',
                    'provider' => 'none',
                    'error' => $e->getMessage(),
                    'response_time' => now()->toISOString()
                ]
            ];
        }
    }

    /**
     * Get conversation history
     */
    public function getConversationHistory($userId, $conversationId)
    {
        $conversation = AiChatConversation::where('id', $conversationId)
            ->where('user_id', $userId)
            ->with(['messages' => function($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->first();

        if (!$conversation) {
            return null;
        }

        return [
            'conversation_id' => $conversation->id,
            'title' => $conversation->title,
            'messages' => $conversation->messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'type' => $message->type,
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'metadata' => $message->metadata
                ];
            })
        ];
    }

    /**
     * Get user's conversation list
     */
    public function getUserConversations($userId, $limit = 10)
    {
        return AiChatConversation::where('user_id', $userId)
            ->where('is_active', true)
            ->with('latestMessage')
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($conversation) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'last_message' => $conversation->latestMessage ?
                        Str::limit($conversation->latestMessage->message, 50) : 'New conversation',
                    'updated_at' => $conversation->updated_at->diffForHumans()
                ];
            });
    }

    /**
     * End conversation
     */
    public function endConversation($userId, $conversationId)
    {
        return AiChatConversation::where('id', $conversationId)
            ->where('user_id', $userId)
            ->update(['is_active' => false]);
    }
}
