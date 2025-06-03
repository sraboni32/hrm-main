<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AiTaskGeneratorService
{
    private $apiKey;
    private $apiUrl;
    private $model;
    private $maxTokens;
    private $temperature;

    public function __construct()
    {
        // Use NVIDIA API by default, fallback to OpenAI
        $this->apiKey = config('services.nvidia_ai.api_key');
        $baseUrl = config('services.nvidia_ai.base_url', 'https://integrate.api.nvidia.com/v1');
        $this->apiUrl = rtrim($baseUrl, '/') . '/chat/completions';
        $this->model = config('services.nvidia_ai.model', 'nvidia/llama-3.3-nemotron-super-49b-v1');
        $this->maxTokens = (int) config('services.nvidia_ai.max_tokens', 4096);
        $this->temperature = (float) config('services.nvidia_ai.temperature', 0.7);
    }

    /**
     * Generate tasks for a project using AI
     */
    public function generateTasksForProject($projectData, $options = [])
    {
        try {
            // Prepare project context
            $projectContext = $this->prepareProjectContext($projectData, $options);

            // Get AI-generated tasks
            $aiResponse = $this->callAI($projectContext);

            // Parse and structure the response
            $tasks = $this->parseAIResponse($aiResponse, $projectData);

            // Validate and enhance tasks
            $validatedTasks = $this->validateAndEnhanceTasks($tasks, $projectData);

            return [
                'success' => true,
                'tasks' => $validatedTasks,
                'metadata' => [
                    'total_tasks' => count($validatedTasks),
                    'estimated_duration' => $this->calculateTotalDuration($validatedTasks),
                    'ai_model' => $this->model
                ]
            ];

        } catch (\Exception $e) {
            Log::error('AI Task Generation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to generate tasks: ' . $e->getMessage(),
                'tasks' => []
            ];
        }
    }

    /**
     * Prepare project context for AI
     */
    private function prepareProjectContext($projectData, $options)
    {
        // Get available employees and their skills
        $availableEmployees = $this->getAvailableEmployees($projectData['company_id'] ?? null);

        // Get similar projects for reference
        $similarProjects = $this->getSimilarProjects($projectData);

        $context = [
            'project' => $projectData,
            'options' => array_merge([
                'task_complexity' => 'medium',
                'team_size' => 'auto',
                'include_testing' => true,
                'include_documentation' => true,
                'methodology' => 'agile'
            ], $options),
            'available_employees' => $availableEmployees,
            'similar_projects' => $similarProjects,
            'current_date' => Carbon::now()->toDateString()
        ];

        return $context;
    }

    /**
     * Call AI API to generate tasks
     */
    private function callAI($context)
    {
        $systemPrompt = $this->buildSystemPrompt();
        $userPrompt = $this->buildUserPrompt($context);

        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ],
            [
                'role' => 'user',
                'content' => $userPrompt
            ]
        ];

        try {
            // Log the prompt for debugging
            Log::info('AI Task Generation Prompt:', [
                'system_prompt_length' => strlen($messages[0]['content']),
                'user_prompt' => $messages[1]['content'],
                'model' => $this->model
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'top_p' => 0.95,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            ]);

            if (!$response->successful()) {
                throw new \Exception('AI API request failed: ' . $response->body());
            }

            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? '';

        } catch (\Exception $e) {
            // Fallback to OpenAI if NVIDIA fails
            return $this->fallbackToOpenAI($messages);
        }
    }

    /**
     * Build system prompt for AI
     */
    private function buildSystemPrompt()
    {
        return "You are an expert project manager and task breakdown specialist. Your job is to analyze specific project details and generate a comprehensive, PROJECT-SPECIFIC list of tasks needed to complete that exact project successfully.

CRITICAL: You must create tasks that are SPECIFIC to the project described, not generic software development tasks. Analyze the project title, description, and requirements to understand what type of project this is and what specific deliverables are needed.

IMPORTANT: You must respond with a valid JSON array of tasks. Each task must have the following structure:
{
    \"title\": \"Specific task title related to this project (max 255 characters)\",
    \"summary\": \"Brief task summary specific to project needs (max 255 characters)\",
    \"description\": \"Detailed task description explaining what needs to be done for THIS project\",
    \"priority\": \"high|medium|low\",
    \"estimated_hours\": \"number (e.g., 8, 16, 24)\",
    \"dependencies\": [\"list of task titles this depends on\"],
    \"suggested_assignee_skills\": [\"required skills for this specific task\"],
    \"task_type\": \"development|design|testing|documentation|planning|review|research|integration|deployment\",
    \"milestone\": \"which project phase this belongs to\"
}

Guidelines for PROJECT-SPECIFIC task generation:
1. READ the project title and description carefully - understand what type of project this is
2. If it's an e-commerce site, include tasks like 'Product catalog setup', 'Payment gateway integration'
3. If it's a mobile app, include tasks like 'UI/UX design for mobile', 'App store submission'
4. If it's a CRM system, include tasks like 'Customer data migration', 'Sales pipeline setup'
5. If it's a website, include tasks like 'Content creation', 'SEO optimization'
6. Include industry-specific features mentioned in requirements
7. Break down complex features into specific sub-tasks
8. Consider the actual business domain and user needs
9. Include data migration, integrations, and third-party services if relevant
10. Make task titles and descriptions reflect the actual project scope

AVOID generic tasks like 'Core Development' - instead use specific tasks like 'Implement user authentication system' or 'Build product search functionality'.

Respond ONLY with the JSON array, no additional text or formatting.";
    }

    /**
     * Build user prompt with project context
     */
    private function buildUserPrompt($context)
    {
        $project = $context['project'];
        $options = $context['options'];

        $prompt = "Generate PROJECT-SPECIFIC tasks for the following project. Analyze the project details carefully and create tasks that are tailored to this specific project's needs and deliverables:\n\n";

        $prompt .= "=== PROJECT ANALYSIS ===\n";
        $prompt .= "Title: " . ($project['title'] ?? 'Untitled Project') . "\n";
        $prompt .= "Summary: " . ($project['summary'] ?? 'No summary provided') . "\n";
        $prompt .= "Description: " . ($project['description'] ?? 'No description provided') . "\n";
        $prompt .= "Priority: " . ($project['priority'] ?? 'medium') . "\n";
        $prompt .= "Start Date: " . ($project['start_date'] ?? 'Not specified') . "\n";
        $prompt .= "End Date: " . ($project['end_date'] ?? 'Not specified') . "\n";

        // Calculate project duration for context
        if (!empty($project['start_date']) && !empty($project['end_date'])) {
            $startDate = Carbon::parse($project['start_date']);
            $endDate = Carbon::parse($project['end_date']);
            $duration = $startDate->diffInDays($endDate);
            $prompt .= "Project Duration: " . $duration . " days\n";
        }

        if (!empty($project['client_requirements'])) {
            $prompt .= "\n=== CLIENT REQUIREMENTS ===\n";
            $prompt .= $project['client_requirements'] . "\n";
        }

        if (!empty($project['technical_requirements'])) {
            $prompt .= "\n=== TECHNICAL REQUIREMENTS ===\n";
            $prompt .= $project['technical_requirements'] . "\n";
        }

        $prompt .= "\n=== PROJECT CONFIGURATION ===\n";
        $prompt .= "Complexity Level: " . $options['task_complexity'] . "\n";
        $prompt .= "Development Methodology: " . $options['methodology'] . "\n";
        $prompt .= "Include Testing Tasks: " . ($options['include_testing'] ? 'Yes' : 'No') . "\n";
        $prompt .= "Include Documentation Tasks: " . ($options['include_documentation'] ? 'Yes' : 'No') . "\n";

        if (!empty($context['available_employees'])) {
            $prompt .= "\n=== AVAILABLE TEAM & SKILLS ===\n";
            $departments = [];
            foreach ($context['available_employees'] as $emp) {
                $dept = $emp['department'];
                if (!isset($departments[$dept])) {
                    $departments[$dept] = [];
                }
                $departments[$dept][] = $emp['name'];
            }

            foreach ($departments as $dept => $employees) {
                $prompt .= "• " . $dept . ": " . implode(', ', $employees) . "\n";
            }
        }

        if (!empty($context['similar_projects'])) {
            $prompt .= "\n=== SIMILAR COMPLETED PROJECTS (for reference) ===\n";
            foreach ($context['similar_projects'] as $similar) {
                $prompt .= "• " . $similar['title'] . " (Duration: " . $similar['duration'] . " days, Priority: " . $similar['priority'] . ")\n";
            }
        }

        $prompt .= "\n=== TASK GENERATION INSTRUCTIONS ===\n";
        $prompt .= "Based on the project analysis above, generate specific tasks that are:\n";
        $prompt .= "1. DIRECTLY related to the project title and description\n";
        $prompt .= "2. Specific to the business domain and requirements mentioned\n";
        $prompt .= "3. Broken down into actionable, measurable deliverables\n";
        $prompt .= "4. Realistic for the given timeline and team skills\n";
        $prompt .= "5. Include specific features, integrations, and functionality mentioned\n";
        $prompt .= "6. Consider the project's unique challenges and opportunities\n\n";

        $prompt .= "AVOID generic tasks - make each task specific to THIS project's goals and deliverables.";

        return $prompt;
    }

    /**
     * Fallback to OpenAI if NVIDIA fails
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
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => $messages,
                'max_tokens' => config('services.openai.max_tokens', 2000),
                'temperature' => config('services.openai.temperature', 0.7),
            ]);

            if (!$response->successful()) {
                throw new \Exception('Fallback API also failed');
            }

            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? '';

        } catch (\Exception $e) {
            throw new \Exception('Both AI APIs failed: ' . $e->getMessage());
        }
    }

    /**
     * Parse AI response and extract tasks
     */
    private function parseAIResponse($aiResponse, $projectData)
    {
        try {
            // Clean the response - remove any markdown formatting
            $cleanResponse = trim($aiResponse);
            $cleanResponse = preg_replace('/```json\s*/', '', $cleanResponse);
            $cleanResponse = preg_replace('/```\s*$/', '', $cleanResponse);
            $cleanResponse = trim($cleanResponse);

            // Try to decode JSON
            $tasks = json_decode($cleanResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from AI: ' . json_last_error_msg());
            }

            if (!is_array($tasks)) {
                throw new \Exception('AI response is not an array');
            }

            return $tasks;

        } catch (\Exception $e) {
            Log::error('Failed to parse AI response: ' . $e->getMessage());
            Log::error('AI Response: ' . $aiResponse);

            // Return fallback tasks if parsing fails
            return $this->getFallbackTasks($projectData);
        }
    }

    /**
     * Validate and enhance generated tasks
     */
    private function validateAndEnhanceTasks($tasks, $projectData)
    {
        $validatedTasks = [];
        $projectStartDate = Carbon::parse($projectData['start_date'] ?? Carbon::now());
        $projectEndDate = Carbon::parse($projectData['end_date'] ?? Carbon::now()->addDays(30));

        foreach ($tasks as $index => $task) {
            // Validate required fields
            if (empty($task['title']) || empty($task['summary'])) {
                continue;
            }

            // Calculate task dates based on dependencies and project timeline
            $taskDates = $this->calculateTaskDates($task, $index, $projectStartDate, $projectEndDate, $validatedTasks);

            $validatedTask = [
                'title' => substr($task['title'], 0, 255),
                'summary' => substr($task['summary'], 0, 255),
                'description' => $task['description'] ?? '',
                'priority' => in_array($task['priority'] ?? '', ['high', 'medium', 'low']) ? $task['priority'] : 'medium',
                'estimated_hour' => $this->validateEstimatedHours($task['estimated_hours'] ?? 8),
                'start_date' => $taskDates['start_date'],
                'end_date' => $taskDates['end_date'],
                'status' => 'not_started',
                'task_progress' => '0',
                'project_id' => $projectData['id'] ?? null,
                'company_id' => $projectData['company_id'] ?? null,
                'dependencies' => $task['dependencies'] ?? [],
                'suggested_skills' => $task['suggested_assignee_skills'] ?? [],
                'task_type' => $task['task_type'] ?? 'development',
                'milestone' => $task['milestone'] ?? 'Phase 1',
                'ai_generated' => true
            ];

            $validatedTasks[] = $validatedTask;
        }

        return $validatedTasks;
    }

    /**
     * Calculate task start and end dates
     */
    private function calculateTaskDates($task, $index, $projectStartDate, $projectEndDate, $existingTasks)
    {
        $estimatedHours = $this->validateEstimatedHours($task['estimated_hours'] ?? 8);
        $estimatedDays = max(1, ceil($estimatedHours / 8)); // Assuming 8 hours per day

        // For tasks with dependencies, start after dependent tasks
        $dependencies = $task['dependencies'] ?? [];
        $earliestStartDate = $projectStartDate;

        if (!empty($dependencies)) {
            foreach ($existingTasks as $existingTask) {
                if (in_array($existingTask['title'], $dependencies)) {
                    $dependentEndDate = Carbon::parse($existingTask['end_date']);
                    if ($dependentEndDate->isAfter($earliestStartDate)) {
                        $earliestStartDate = $dependentEndDate->addDay();
                    }
                }
            }
        } else {
            // For tasks without dependencies, distribute them across the project timeline
            $daysFromStart = floor($index * 2); // Stagger tasks by 2 days
            $earliestStartDate = $projectStartDate->copy()->addDays($daysFromStart);
        }

        $startDate = $earliestStartDate;
        $endDate = $startDate->copy()->addDays($estimatedDays - 1);

        // Ensure task doesn't exceed project end date
        if ($endDate->isAfter($projectEndDate)) {
            $endDate = $projectEndDate;
            $startDate = $endDate->copy()->subDays($estimatedDays - 1);
            if ($startDate->isBefore($projectStartDate)) {
                $startDate = $projectStartDate;
            }
        }

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ];
    }

    /**
     * Validate estimated hours
     */
    private function validateEstimatedHours($hours)
    {
        $hours = is_numeric($hours) ? (int)$hours : 8;
        return max(1, min(200, $hours)); // Between 1 and 200 hours
    }

    /**
     * Calculate total project duration
     */
    private function calculateTotalDuration($tasks)
    {
        $totalHours = array_sum(array_column($tasks, 'estimated_hour'));
        return [
            'total_hours' => $totalHours,
            'estimated_days' => ceil($totalHours / 8),
            'estimated_weeks' => ceil($totalHours / 40)
        ];
    }

    /**
     * Get available employees for task assignment
     */
    private function getAvailableEmployees($companyId = null)
    {
        $query = Employee::with(['department', 'designation'])
            ->where('deleted_at', null);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => trim($employee->firstname . ' ' . $employee->lastname),
                'department' => $employee->department->department_name ?? 'No Department',
                'designation' => $employee->designation->designation ?? 'No Designation',
                'skills' => $this->extractEmployeeSkills($employee)
            ];
        })->toArray();
    }

    /**
     * Extract employee skills (placeholder - can be enhanced)
     */
    private function extractEmployeeSkills($employee)
    {
        // This is a placeholder - you can enhance this based on your employee data structure
        $skills = [];

        if ($employee->department) {
            $deptName = strtolower($employee->department->department_name);
            if (strpos($deptName, 'development') !== false || strpos($deptName, 'tech') !== false) {
                $skills[] = 'Programming';
                $skills[] = 'Software Development';
            }
            if (strpos($deptName, 'design') !== false) {
                $skills[] = 'UI/UX Design';
                $skills[] = 'Graphic Design';
            }
            if (strpos($deptName, 'marketing') !== false) {
                $skills[] = 'Digital Marketing';
                $skills[] = 'Content Creation';
            }
            if (strpos($deptName, 'qa') !== false || strpos($deptName, 'test') !== false) {
                $skills[] = 'Quality Assurance';
                $skills[] = 'Testing';
            }
        }

        return $skills;
    }

    /**
     * Get similar projects for reference
     */
    private function getSimilarProjects($projectData)
    {
        $similarProjects = [];

        try {
            $query = Project::where('deleted_at', null)
                ->where('status', 'completed');

            if (!empty($projectData['company_id'])) {
                $query->where('company_id', $projectData['company_id']);
            }

            $projects = $query->limit(3)->get();

            foreach ($projects as $project) {
                $startDate = Carbon::parse($project->start_date);
                $endDate = Carbon::parse($project->end_date);
                $duration = $startDate->diffInDays($endDate);

                $similarProjects[] = [
                    'title' => $project->title,
                    'duration' => $duration,
                    'priority' => $project->priority,
                    'status' => $project->status
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get similar projects: ' . $e->getMessage());
        }

        return $similarProjects;
    }

    /**
     * Get fallback tasks if AI generation fails
     */
    private function getFallbackTasks($projectData)
    {
        return [
            [
                'title' => 'Project Planning and Requirements Analysis',
                'summary' => 'Define project scope, requirements, and create project plan',
                'description' => 'Analyze project requirements, create detailed specifications, and establish project timeline and milestones.',
                'priority' => 'high',
                'estimated_hours' => 16,
                'dependencies' => [],
                'suggested_assignee_skills' => ['Project Management', 'Business Analysis'],
                'task_type' => 'planning',
                'milestone' => 'Planning Phase'
            ],
            [
                'title' => 'System Design and Architecture',
                'summary' => 'Create system architecture and technical design',
                'description' => 'Design system architecture, database schema, and technical specifications.',
                'priority' => 'high',
                'estimated_hours' => 24,
                'dependencies' => ['Project Planning and Requirements Analysis'],
                'suggested_assignee_skills' => ['System Architecture', 'Technical Design'],
                'task_type' => 'design',
                'milestone' => 'Design Phase'
            ],
            [
                'title' => 'Core Development',
                'summary' => 'Implement core functionality',
                'description' => 'Develop the main features and functionality of the project.',
                'priority' => 'high',
                'estimated_hours' => 40,
                'dependencies' => ['System Design and Architecture'],
                'suggested_assignee_skills' => ['Programming', 'Software Development'],
                'task_type' => 'development',
                'milestone' => 'Development Phase'
            ],
            [
                'title' => 'Testing and Quality Assurance',
                'summary' => 'Test functionality and ensure quality',
                'description' => 'Perform comprehensive testing including unit tests, integration tests, and user acceptance testing.',
                'priority' => 'medium',
                'estimated_hours' => 16,
                'dependencies' => ['Core Development'],
                'suggested_assignee_skills' => ['Testing', 'Quality Assurance'],
                'task_type' => 'testing',
                'milestone' => 'Testing Phase'
            ],
            [
                'title' => 'Documentation and Deployment',
                'summary' => 'Create documentation and deploy to production',
                'description' => 'Create user documentation, technical documentation, and deploy the project to production environment.',
                'priority' => 'medium',
                'estimated_hours' => 12,
                'dependencies' => ['Testing and Quality Assurance'],
                'suggested_assignee_skills' => ['Documentation', 'DevOps'],
                'task_type' => 'documentation',
                'milestone' => 'Deployment Phase'
            ]
        ];
    }

    /**
     * Create tasks in database
     */
    public function createTasksInDatabase($tasks, $projectId)
    {
        $createdTasks = [];

        try {
            foreach ($tasks as $taskData) {
                $taskData['project_id'] = $projectId;

                $task = Task::create([
                    'title' => $taskData['title'],
                    'summary' => $taskData['summary'],
                    'description' => $taskData['description'],
                    'priority' => $taskData['priority'],
                    'estimated_hour' => $taskData['estimated_hour'],
                    'start_date' => $taskData['start_date'],
                    'end_date' => $taskData['end_date'],
                    'status' => $taskData['status'],
                    'task_progress' => $taskData['task_progress'],
                    'project_id' => $taskData['project_id'],
                    'company_id' => $taskData['company_id'],
                    'note' => 'AI Generated Task - ' . ($taskData['milestone'] ?? 'No milestone')
                ]);

                $createdTasks[] = $task;
            }

            return [
                'success' => true,
                'created_tasks' => $createdTasks,
                'count' => count($createdTasks)
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create tasks in database: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'created_tasks' => $createdTasks
            ];
        }
    }
}
