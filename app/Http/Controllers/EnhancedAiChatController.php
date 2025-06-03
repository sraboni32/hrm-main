<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\EnhancedAiHrService;
use App\Services\SemanticAnalysisService;
use App\Services\VectorSearchService;
use App\Services\AiChatService;
use App\Services\QuestionSuggestionService;
use App\Services\FollowUpQuestionGenerator;
use App\Models\AiChatConversation;
use App\Models\AiChatMessage;

/**
 * Enhanced AI Chat Controller - 2024 Best Practices Implementation
 *
 * Features:
 * - RAG-powered semantic understanding
 * - Vector-based schema retrieval
 * - Advanced natural language processing
 * - Enterprise-grade security and audit
 * - Multi-modal AI responses
 */
class EnhancedAiChatController extends Controller
{
    private $enhancedAiService;
    private $semanticService;
    private $vectorService;
    private $chatService;
    private $suggestionService;
    private $followUpGenerator;

    public function __construct()
    {
        $this->middleware('auth');
        $this->semanticService = new SemanticAnalysisService();
        $this->vectorService = new VectorSearchService();
        $this->chatService = new AiChatService();
        $this->suggestionService = new QuestionSuggestionService();
        $this->followUpGenerator = new FollowUpQuestionGenerator();
    }

    /**
     * Enhanced chat interface with advanced AI capabilities
     */
    public function chat(Request $request)
    {
        try {
            $user = Auth::user();
            $this->enhancedAiService = new EnhancedAiHrService($user->id);

            // Get or create conversation
            $conversation = $this->getOrCreateConversation($request, $user);

            // Enhanced message processing
            $response = $this->processEnhancedMessage($request, $conversation, $user);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Enhanced AI Chat Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing your request.',
                'fallback_response' => $this->generateFallbackResponse($request->input('message'))
            ], 500);
        }
    }

    /**
     * Process message with enhanced AI capabilities
     */
    private function processEnhancedMessage($request, $conversation, $user)
    {
        $message = $request->input('message');
        $context = $request->input('context', []);

        // Step 1: Semantic Analysis
        $semanticAnalysis = $this->semanticService->analyzeQuery($message, $context);

        // Step 2: Determine processing approach based on user role and query complexity
        $processingApproach = $this->determineProcessingApproach($user, $semanticAnalysis);

        // Step 3: Execute query with appropriate method
        $queryResult = $this->executeEnhancedQuery($message, $semanticAnalysis, $processingApproach);

        // Step 4: Generate AI response
        $aiResponse = $this->generateEnhancedAiResponse($message, $queryResult, $semanticAnalysis, $conversation);

        // Step 5: Save conversation
        $this->saveEnhancedConversation($conversation, $message, $aiResponse, $queryResult);

        return [
            'success' => true,
            'response' => $aiResponse['content'],
            'metadata' => [
                'semantic_analysis' => $semanticAnalysis,
                'processing_approach' => $processingApproach,
                'confidence_score' => $queryResult['confidence_score'] ?? 0.8,
                'execution_time' => $queryResult['performance_metrics']['execution_time'] ?? null,
                'data_insights' => $queryResult['data']['insights'] ?? null,
                'recommendations' => $queryResult['data']['recommendations'] ?? null
            ],
            'conversation_id' => $conversation->id,
            'suggested_followups' => $this->generateEnhancedFollowups($message, $queryResult, $semanticAnalysis)
        ];
    }

    /**
     * Determine best processing approach based on user and query
     */
    private function determineProcessingApproach($user, $semanticAnalysis)
    {
        $approach = [
            'method' => 'enhanced_rag', // Default to RAG approach
            'security_level' => 'standard',
            'response_format' => 'conversational',
            'include_sql' => false,
            'include_insights' => true
        ];

        // Super admin gets full access
        if ($user->role_users_id == 1) {
            $approach['method'] = 'direct_database';
            $approach['security_level'] = 'full_access';
            $approach['include_sql'] = false; // Still hide SQL from UI as requested
            $approach['include_performance'] = true;
        }

        // Adjust based on query complexity
        if ($semanticAnalysis['complexity_level'] === 'complex') {
            $approach['method'] = 'enhanced_rag';
            $approach['include_insights'] = true;
        }

        // Adjust based on confidentiality
        if ($semanticAnalysis['domain_context']['confidentiality_level'] === 'high') {
            $approach['security_level'] = 'high';
            $approach['audit_level'] = 'detailed';
        }

        return $approach;
    }

    /**
     * Execute query with enhanced processing
     */
    private function executeEnhancedQuery($message, $semanticAnalysis, $processingApproach)
    {
        switch ($processingApproach['method']) {
            case 'direct_database':
                return $this->enhancedAiService->executeEnhancedQuery($message, [
                    'semantic_analysis' => $semanticAnalysis,
                    'processing_approach' => $processingApproach
                ]);

            case 'enhanced_rag':
                return $this->executeRagBasedQuery($message, $semanticAnalysis);

            default:
                return $this->executeFallbackQuery($message, $semanticAnalysis);
        }
    }

    /**
     * Execute RAG-based query processing
     */
    private function executeRagBasedQuery($message, $semanticAnalysis)
    {
        try {
            // Use vector search to find relevant schema
            $relevantSchema = $this->vectorService->findRelevantSchema($semanticAnalysis);

            // Generate context-aware response
            $response = $this->generateRagResponse($message, $semanticAnalysis, $relevantSchema);

            return [
                'success' => true,
                'data' => $response,
                'confidence_score' => $this->calculateRagConfidence($semanticAnalysis, $relevantSchema),
                'method' => 'rag_based'
            ];

        } catch (\Exception $e) {
            Log::error('RAG Query Error: ' . $e->getMessage());
            return $this->executeFallbackQuery($message, $semanticAnalysis);
        }
    }

    /**
     * Generate RAG-based response
     */
    private function generateRagResponse($message, $semanticAnalysis, $relevantSchema)
    {
        // Create context from relevant schema
        $context = $this->buildRagContext($relevantSchema, $semanticAnalysis);

        // Generate response using traditional AI chat service with enhanced context
        $enhancedMessage = $this->enhanceMessageWithContext($message, $context);

        // Use existing AI chat service but with enhanced context
        $conversation = AiChatConversation::create([
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'title' => substr($message, 0, 50),
            'is_active' => true
        ]);

        $response = $this->chatService->processMessage($conversation, $enhancedMessage);

        return [
            'content' => $response['response'],
            'context_used' => $context,
            'schema_relevance' => $relevantSchema['similarity_scores']
        ];
    }

    /**
     * Build context for RAG processing
     */
    private function buildRagContext($relevantSchema, $semanticAnalysis)
    {
        $context = [];

        // Add relevant tables information
        foreach ($relevantSchema['tables'] as $tableName => $tableData) {
            $context[] = "Table {$tableName}: " . $tableData['metadata']['description'];
        }

        // Add relevant columns information
        foreach ($relevantSchema['columns'] as $columnKey => $columnData) {
            $context[] = "Column {$columnKey}: " . $columnData['metadata']['description'];
        }

        // Add relationship information
        foreach ($relevantSchema['relationships'] as $relationship) {
            $context[] = "Relationship: {$relationship['from_table']} connects to {$relationship['to_table']}";
        }

        return implode('. ', $context);
    }

    /**
     * Enhance message with RAG context
     */
    private function enhanceMessageWithContext($message, $context)
    {
        return "Context: {$context}\n\nUser Question: {$message}\n\nPlease provide a helpful response based on the available HR database information.";
    }

    /**
     * Generate enhanced AI response with insights
     */
    private function generateEnhancedAiResponse($message, $queryResult, $semanticAnalysis, $conversation)
    {
        // Prepare enhanced context for AI
        $enhancedContext = [
            'query_result' => $queryResult,
            'semantic_analysis' => $semanticAnalysis,
            'user_context' => $this->getUserContext(),
            'conversation_history' => $this->getConversationHistory($conversation)
        ];

        // Generate response using existing chat service with enhanced context
        $response = $this->chatService->processMessage($conversation, $message, $enhancedContext);

        // Add insights and recommendations if available
        if (isset($queryResult['data']['insights'])) {
            $response['insights'] = $queryResult['data']['insights'];
        }

        if (isset($queryResult['data']['recommendations'])) {
            $response['recommendations'] = $queryResult['data']['recommendations'];
        }

        return $response;
    }

    /**
     * Generate suggested follow-up questions
     */
    private function generateSuggestedFollowups($semanticAnalysis, $queryResult)
    {
        $followups = [];

        // Based on intent
        switch ($semanticAnalysis['intent']['primary']) {
            case 'count':
                $followups[] = "Show me the detailed breakdown of these numbers";
                $followups[] = "What are the trends over time?";
                break;

            case 'retrieve':
                $followups[] = "Can you analyze this data further?";
                $followups[] = "Show me related information";
                break;

            case 'analyze':
                $followups[] = "What recommendations do you have?";
                $followups[] = "How does this compare to industry standards?";
                break;
        }

        // Based on domain context
        if ($semanticAnalysis['domain_context']['hr_function']) {
            $function = $semanticAnalysis['domain_context']['hr_function'];
            $followups[] = "Show me other {$function} related metrics";
        }

        return array_slice($followups, 0, 3); // Limit to 3 suggestions
    }

    /**
     * Calculate confidence score for RAG responses
     */
    private function calculateRagConfidence($semanticAnalysis, $relevantSchema)
    {
        $factors = [
            'semantic_confidence' => $semanticAnalysis['confidence_scores']['overall'] ?? 0.7,
            'schema_relevance' => $this->calculateSchemaRelevance($relevantSchema),
            'intent_clarity' => $semanticAnalysis['intent']['confidence']
        ];

        return array_sum($factors) / count($factors);
    }

    /**
     * Calculate schema relevance score
     */
    private function calculateSchemaRelevance($relevantSchema)
    {
        if (empty($relevantSchema['similarity_scores'])) {
            return 0.5;
        }

        $scores = array_values($relevantSchema['similarity_scores']);
        return array_sum($scores) / count($scores);
    }

    /**
     * Get user context for enhanced processing
     */
    private function getUserContext()
    {
        $user = Auth::user();
        return [
            'role' => $user->role_users_id == 1 ? 'super_admin' : 'user',
            'department' => $user->employee->department->department_name ?? null,
            'permissions' => $this->getUserPermissions($user)
        ];
    }

    /**
     * Get conversation history for context
     */
    private function getConversationHistory($conversation)
    {
        return $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($message) {
                return [
                    'type' => $message->type,
                    'content' => substr($message->message, 0, 200),
                    'timestamp' => $message->created_at
                ];
            });
    }

    /**
     * Execute fallback query when enhanced methods fail
     */
    private function executeFallbackQuery($message, $semanticAnalysis)
    {
        // Use existing AI agent service as fallback
        $aiAgent = new \App\Services\AiAgentService(Auth::id());
        return $aiAgent->executeQuery($message);
    }

    /**
     * Generate fallback response for errors
     */
    private function generateFallbackResponse($message)
    {
        return "I apologize, but I'm having trouble processing your request right now. Please try rephrasing your question or contact support if the issue persists.";
    }

    /**
     * Get or create conversation
     */
    private function getOrCreateConversation($request, $user)
    {
        $conversationId = $request->input('conversation_id');

        if ($conversationId) {
            return AiChatConversation::where('id', $conversationId)
                ->where('user_id', $user->id)
                ->first();
        }

        return AiChatConversation::create([
            'user_id' => $user->id,
            'session_id' => session()->getId(),
            'title' => substr($request->input('message'), 0, 50),
            'is_active' => true
        ]);
    }

    /**
     * Save enhanced conversation with metadata
     */
    private function saveEnhancedConversation($conversation, $message, $response, $queryResult)
    {
        // Save user message
        AiChatMessage::create([
            'conversation_id' => $conversation->id,
            'type' => 'user',
            'message' => $message,
            'metadata' => json_encode([
                'timestamp' => now(),
                'processing_method' => $queryResult['method'] ?? 'unknown'
            ])
        ]);

        // Save AI response
        AiChatMessage::create([
            'conversation_id' => $conversation->id,
            'type' => 'assistant',
            'message' => $response['content'] ?? $response['response'],
            'metadata' => json_encode([
                'confidence_score' => $queryResult['confidence_score'] ?? null,
                'execution_time' => $queryResult['performance_metrics']['execution_time'] ?? null,
                'method' => $queryResult['method'] ?? 'unknown'
            ])
        ]);
    }

    /**
     * Get user permissions for context
     */
    private function getUserPermissions($user)
    {
        // Simplified permission check
        return [
            'can_view_all_employees' => $user->role_users_id == 1,
            'can_view_salary_data' => $user->role_users_id == 1,
            'can_modify_data' => $user->role_users_id == 1
        ];
    }

    /**
     * Get initial question suggestions for new conversations
     */
    public function getInitialSuggestions(Request $request)
    {
        try {
            $limit = $request->input('limit', 8);
            $category = $request->input('category');

            if ($category) {
                $suggestions = $this->suggestionService->getSuggestionsByCategory($category, $limit);
            } else {
                $suggestions = $this->suggestionService->getInitialSuggestions($limit);
            }

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
                'categories' => $this->suggestionService->getAvailableCategories()
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting initial suggestions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Unable to load suggestions',
                'suggestions' => $this->getFallbackSuggestions()
            ]);
        }
    }

    /**
     * Search question suggestions
     */
    public function searchSuggestions(Request $request)
    {
        try {
            $keyword = $request->input('keyword');
            $limit = $request->input('limit', 10);

            if (empty($keyword)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Keyword is required'
                ]);
            }

            $suggestions = $this->suggestionService->searchSuggestions($keyword, $limit);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
                'keyword' => $keyword
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching suggestions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Unable to search suggestions'
            ]);
        }
    }

    /**
     * Get suggestions by category
     */
    public function getSuggestionsByCategory(Request $request, $category)
    {
        try {
            $limit = $request->input('limit', 10);

            $suggestions = $this->suggestionService->getSuggestionsByCategory($category, $limit);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
                'category' => $category
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting category suggestions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Unable to load category suggestions'
            ]);
        }
    }

    /**
     * Generate enhanced follow-up questions
     */
    private function generateEnhancedFollowups($originalQuestion, $queryResult, $semanticAnalysis)
    {
        try {
            $userContext = $this->getUserContext();

            return $this->followUpGenerator->generateFollowUps(
                $originalQuestion,
                $queryResult,
                $semanticAnalysis,
                $userContext
            );

        } catch (\Exception $e) {
            Log::error('Error generating enhanced follow-ups: ' . $e->getMessage());
            return $this->getFallbackFollowups($originalQuestion);
        }
    }

    /**
     * Get fallback suggestions when services fail
     */
    private function getFallbackSuggestions()
    {
        return [
            ['text' => 'Show me all employees', 'category' => 'employees'],
            ['text' => 'How many employees work here?', 'category' => 'employees'],
            ['text' => 'Show today\'s attendance', 'category' => 'attendance'],
            ['text' => 'What are the active projects?', 'category' => 'projects'],
            ['text' => 'Show department information', 'category' => 'departments'],
            ['text' => 'Who is on leave today?', 'category' => 'leaves']
        ];
    }

    /**
     * Get fallback follow-ups when generation fails
     */
    private function getFallbackFollowups($originalQuestion)
    {
        return [
            ['text' => 'Show me related information', 'category' => 'related'],
            ['text' => 'Can you provide more details?', 'category' => 'details'],
            ['text' => 'What are the recent trends?', 'category' => 'trend'],
            ['text' => 'How does this compare to last month?', 'category' => 'comparison']
        ];
    }
}
