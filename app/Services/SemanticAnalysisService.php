<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Semantic Analysis Service - Advanced NLP for HR Database Queries
 * 
 * Implements 2024 best practices:
 * - Intent detection with confidence scoring
 * - Entity extraction and relationship mapping
 * - Temporal context understanding
 * - Domain-specific semantic understanding
 */
class SemanticAnalysisService
{
    private $hrDomainTerms;
    private $temporalPatterns;
    private $intentPatterns;
    private $entityPatterns;

    public function __construct()
    {
        $this->initializeDomainKnowledge();
    }

    /**
     * Perform comprehensive semantic analysis
     */
    public function analyzeQuery($question, $context = [])
    {
        $analysis = [
            'original_question' => $question,
            'normalized_question' => $this->normalizeQuestion($question),
            'intent' => $this->detectIntent($question),
            'entities' => $this->extractEntities($question),
            'temporal_context' => $this->extractTemporalContext($question),
            'aggregation_type' => $this->detectAggregationType($question),
            'complexity_level' => $this->assessComplexity($question),
            'domain_context' => $this->extractDomainContext($question),
            'user_context' => $context,
            'confidence_scores' => []
        ];

        // Calculate confidence scores for each component
        $analysis['confidence_scores'] = $this->calculateConfidenceScores($analysis);
        
        // Add semantic enrichment
        $analysis['semantic_enrichment'] = $this->enrichSemantics($analysis);
        
        return $analysis;
    }

    /**
     * Detect user intent with confidence scoring
     */
    public function detectIntent($question)
    {
        $question = strtolower($question);
        $intents = [];

        // Define intent patterns with confidence weights
        $intentPatterns = [
            'retrieve' => [
                'patterns' => ['show', 'list', 'get', 'find', 'display', 'what', 'who', 'which'],
                'weight' => 1.0,
                'confidence' => 0.0
            ],
            'count' => [
                'patterns' => ['how many', 'count', 'total', 'number of'],
                'weight' => 1.2,
                'confidence' => 0.0
            ],
            'analyze' => [
                'patterns' => ['analyze', 'analysis', 'compare', 'trend', 'pattern'],
                'weight' => 1.1,
                'confidence' => 0.0
            ],
            'aggregate' => [
                'patterns' => ['average', 'sum', 'total', 'maximum', 'minimum', 'avg', 'max', 'min'],
                'weight' => 1.3,
                'confidence' => 0.0
            ],
            'filter' => [
                'patterns' => ['where', 'filter', 'with', 'having', 'condition'],
                'weight' => 0.8,
                'confidence' => 0.0
            ],
            'update' => [
                'patterns' => ['update', 'change', 'modify', 'set', 'edit'],
                'weight' => 1.5,
                'confidence' => 0.0
            ],
            'create' => [
                'patterns' => ['create', 'add', 'insert', 'new'],
                'weight' => 1.4,
                'confidence' => 0.0
            ],
            'delete' => [
                'patterns' => ['delete', 'remove', 'drop'],
                'weight' => 1.6,
                'confidence' => 0.0
            ]
        ];

        // Calculate confidence for each intent
        foreach ($intentPatterns as $intent => $config) {
            $confidence = 0;
            foreach ($config['patterns'] as $pattern) {
                if (strpos($question, $pattern) !== false) {
                    $confidence += $config['weight'];
                }
            }
            
            if ($confidence > 0) {
                $intents[$intent] = $confidence;
            }
        }

        // Normalize confidence scores
        $maxConfidence = max($intents) ?: 1;
        foreach ($intents as $intent => $confidence) {
            $intents[$intent] = round($confidence / $maxConfidence, 3);
        }

        // Return primary intent with confidence
        $primaryIntent = array_keys($intents, max($intents))[0] ?? 'retrieve';
        
        return [
            'primary' => $primaryIntent,
            'all_intents' => $intents,
            'confidence' => $intents[$primaryIntent] ?? 0.5
        ];
    }

    /**
     * Extract entities (tables, columns, values) from question
     */
    public function extractEntities($question)
    {
        $entities = [
            'tables' => [],
            'columns' => [],
            'values' => [],
            'relationships' => []
        ];

        // HR-specific entity patterns
        $tableEntities = [
            'employees' => ['employee', 'staff', 'worker', 'personnel', 'team member'],
            'departments' => ['department', 'dept', 'division', 'unit'],
            'projects' => ['project', 'initiative', 'assignment'],
            'tasks' => ['task', 'activity', 'work', 'assignment'],
            'attendance' => ['attendance', 'presence', 'clock', 'time'],
            'leaves' => ['leave', 'vacation', 'holiday', 'absence'],
            'salary_disbursements' => ['salary', 'pay', 'wage', 'compensation', 'payment'],
            'clients' => ['client', 'customer', 'account'],
            'companies' => ['company', 'organization', 'firm'],
            'designations' => ['designation', 'position', 'title', 'role']
        ];

        $columnEntities = [
            'name' => ['name', 'firstname', 'lastname'],
            'email' => ['email', 'mail'],
            'salary' => ['salary', 'pay', 'wage', 'compensation'],
            'date' => ['date', 'time', 'when'],
            'status' => ['status', 'state', 'condition'],
            'department' => ['department', 'dept'],
            'project' => ['project'],
            'manager' => ['manager', 'supervisor', 'head']
        ];

        // Extract table entities
        foreach ($tableEntities as $table => $synonyms) {
            foreach ($synonyms as $synonym) {
                if (stripos($question, $synonym) !== false) {
                    $entities['tables'][] = $table;
                    break;
                }
            }
        }

        // Extract column entities
        foreach ($columnEntities as $column => $synonyms) {
            foreach ($synonyms as $synonym) {
                if (stripos($question, $synonym) !== false) {
                    $entities['columns'][] = $column;
                    break;
                }
            }
        }

        // Extract numeric values
        preg_match_all('/\b\d+\b/', $question, $numbers);
        $entities['values']['numbers'] = $numbers[0];

        // Extract quoted strings
        preg_match_all('/"([^"]*)"/', $question, $quotes);
        $entities['values']['strings'] = $quotes[1];

        // Remove duplicates
        $entities['tables'] = array_unique($entities['tables']);
        $entities['columns'] = array_unique($entities['columns']);

        return $entities;
    }

    /**
     * Extract temporal context from question
     */
    public function extractTemporalContext($question)
    {
        $temporal = [
            'period' => null,
            'specific_date' => null,
            'relative_time' => null,
            'time_range' => null
        ];

        // Temporal patterns
        $patterns = [
            'today' => ['today', 'now'],
            'yesterday' => ['yesterday'],
            'this_week' => ['this week', 'current week'],
            'last_week' => ['last week', 'previous week'],
            'this_month' => ['this month', 'current month'],
            'last_month' => ['last month', 'previous month'],
            'this_year' => ['this year', 'current year'],
            'last_year' => ['last year', 'previous year'],
            'this_quarter' => ['this quarter', 'current quarter'],
            'last_quarter' => ['last quarter', 'previous quarter']
        ];

        foreach ($patterns as $period => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($question, $keyword) !== false) {
                    $temporal['period'] = $period;
                    $temporal['relative_time'] = $keyword;
                    break 2;
                }
            }
        }

        // Extract specific dates (YYYY-MM-DD, MM/DD/YYYY, etc.)
        if (preg_match('/\b\d{4}-\d{2}-\d{2}\b/', $question, $matches)) {
            $temporal['specific_date'] = $matches[0];
        }

        // Extract year mentions
        if (preg_match('/\b(19|20)\d{2}\b/', $question, $matches)) {
            $temporal['year'] = $matches[0];
        }

        return $temporal;
    }

    /**
     * Detect aggregation type needed
     */
    public function detectAggregationType($question)
    {
        $aggregations = [];
        
        $patterns = [
            'COUNT' => ['count', 'how many', 'number of', 'total number'],
            'SUM' => ['sum', 'total', 'add up'],
            'AVG' => ['average', 'avg', 'mean'],
            'MAX' => ['maximum', 'max', 'highest', 'largest', 'most'],
            'MIN' => ['minimum', 'min', 'lowest', 'smallest', 'least'],
            'GROUP' => ['by', 'group', 'category', 'department', 'type']
        ];

        foreach ($patterns as $agg => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($question, $keyword) !== false) {
                    $aggregations[] = $agg;
                    break;
                }
            }
        }

        return array_unique($aggregations);
    }

    /**
     * Assess query complexity
     */
    public function assessComplexity($question)
    {
        $complexity = 0;
        
        // Complexity indicators
        $indicators = [
            'joins' => ['with', 'and', 'including', 'along with'],
            'conditions' => ['where', 'if', 'when', 'condition'],
            'aggregations' => ['count', 'sum', 'average', 'total'],
            'sorting' => ['order', 'sort', 'arrange', 'rank'],
            'grouping' => ['group', 'category', 'by'],
            'subqueries' => ['in', 'exists', 'any', 'all']
        ];

        foreach ($indicators as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($question, $keyword) !== false) {
                    $complexity++;
                    break;
                }
            }
        }

        // Classify complexity
        if ($complexity <= 1) return 'simple';
        if ($complexity <= 3) return 'medium';
        return 'complex';
    }

    /**
     * Extract HR domain-specific context
     */
    public function extractDomainContext($question)
    {
        $context = [
            'hr_function' => null,
            'business_process' => null,
            'compliance_related' => false,
            'confidentiality_level' => 'normal'
        ];

        // HR function mapping
        $hrFunctions = [
            'recruitment' => ['hire', 'recruit', 'interview', 'candidate'],
            'performance' => ['performance', 'review', 'evaluation', 'rating'],
            'compensation' => ['salary', 'pay', 'bonus', 'compensation'],
            'benefits' => ['benefit', 'insurance', 'leave', 'vacation'],
            'training' => ['training', 'development', 'skill', 'course'],
            'compliance' => ['compliance', 'policy', 'regulation', 'audit']
        ];

        foreach ($hrFunctions as $function => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($question, $keyword) !== false) {
                    $context['hr_function'] = $function;
                    break 2;
                }
            }
        }

        // Check for confidential data requests
        $confidentialTerms = ['salary', 'personal', 'private', 'confidential'];
        foreach ($confidentialTerms as $term) {
            if (stripos($question, $term) !== false) {
                $context['confidentiality_level'] = 'high';
                break;
            }
        }

        return $context;
    }

    /**
     * Calculate confidence scores for analysis components
     */
    private function calculateConfidenceScores($analysis)
    {
        return [
            'intent' => $analysis['intent']['confidence'],
            'entity_extraction' => $this->calculateEntityConfidence($analysis['entities']),
            'temporal_context' => $this->calculateTemporalConfidence($analysis['temporal_context']),
            'domain_context' => $this->calculateDomainConfidence($analysis['domain_context']),
            'overall' => 0 // Will be calculated as weighted average
        ];
    }

    /**
     * Initialize domain knowledge
     */
    private function initializeDomainKnowledge()
    {
        $this->hrDomainTerms = Cache::remember('hr_domain_terms', 3600, function() {
            return $this->loadHrDomainTerms();
        });
    }

    /**
     * Load HR domain-specific terminology
     */
    private function loadHrDomainTerms()
    {
        return [
            'employee_lifecycle' => [
                'onboarding', 'hiring', 'termination', 'resignation', 'promotion'
            ],
            'performance_management' => [
                'appraisal', 'review', 'goal', 'objective', 'kpi', 'performance'
            ],
            'compensation_benefits' => [
                'salary', 'wage', 'bonus', 'incentive', 'benefit', 'insurance'
            ],
            'time_attendance' => [
                'attendance', 'leave', 'vacation', 'sick', 'overtime', 'schedule'
            ],
            'organizational_structure' => [
                'department', 'team', 'hierarchy', 'reporting', 'manager'
            ]
        ];
    }

    // Additional helper methods for confidence calculations...
    private function calculateEntityConfidence($entities)
    {
        $totalEntities = count($entities['tables']) + count($entities['columns']);
        return $totalEntities > 0 ? min(1.0, $totalEntities * 0.2) : 0.3;
    }

    private function calculateTemporalConfidence($temporal)
    {
        return $temporal['period'] ? 0.9 : 0.5;
    }

    private function calculateDomainConfidence($domain)
    {
        return $domain['hr_function'] ? 0.8 : 0.6;
    }

    private function normalizeQuestion($question)
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $question)));
    }

    private function enrichSemantics($analysis)
    {
        return [
            'suggested_tables' => $this->suggestRelevantTables($analysis),
            'suggested_columns' => $this->suggestRelevantColumns($analysis),
            'query_complexity_estimate' => $this->estimateQueryComplexity($analysis)
        ];
    }

    private function suggestRelevantTables($analysis)
    {
        // Logic to suggest most relevant tables based on analysis
        return $analysis['entities']['tables'] ?: ['employees'];
    }

    private function suggestRelevantColumns($analysis)
    {
        // Logic to suggest most relevant columns based on analysis
        return $analysis['entities']['columns'] ?: ['id', 'firstname', 'lastname'];
    }

    private function estimateQueryComplexity($analysis)
    {
        $score = 0;
        $score += count($analysis['entities']['tables']) * 2;
        $score += count($analysis['entities']['columns']);
        $score += count($analysis['aggregation_type']) * 3;
        
        if ($score <= 3) return 'simple';
        if ($score <= 8) return 'medium';
        return 'complex';
    }
}
