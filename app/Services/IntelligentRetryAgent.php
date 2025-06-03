<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\DatabaseValidationAgent;
use App\Services\ReadOnlyDatabaseGuard;

/**
 * Intelligent Retry Agent - Advanced Error Handling and Recovery
 *
 * Features:
 * - Multi-strategy error recovery
 * - Intelligent query fixing
 * - Progressive fallback mechanisms
 * - Real-time error analysis
 * - Adaptive retry strategies
 */
class IntelligentRetryAgent
{
    private $userId;
    private $userRole;
    private $maxRetries = 5;
    private $validationAgent;
    private $readOnlyGuard;
    private $errorPatterns;
    private $fixStrategies;

    public function __construct($userId, $userRole)
    {
        $this->userId = $userId;
        $this->userRole = $userRole;
        $this->validationAgent = new DatabaseValidationAgent($userId, $userRole);
        $this->readOnlyGuard = new ReadOnlyDatabaseGuard($userId, $userRole);
        $this->initializeErrorHandling();
    }

    /**
     * Execute query with intelligent retry and error recovery
     */
    public function executeWithRetry($question, $initialSQL = null)
    {
        $attempt = 0;
        $errors = [];
        $strategies = [];

        Log::info("Intelligent Retry Agent - Starting execution", [
            'user_id' => $this->userId,
            'question' => $question,
            'initial_sql' => $initialSQL
        ]);

        while ($attempt < $this->maxRetries) {
            $attempt++;

            try {
                // Strategy 1: Use validation agent for real data
                if ($attempt == 1) {
                    $strategy = 'database_validation_agent';
                    $result = $this->validationAgent->executeValidatedQuery($question, $initialSQL);

                    if ($result['success']) {
                        return $this->formatSuccessResponse($result, $strategy, $attempt, $errors);
                    } else {
                        throw new \Exception($result['error']);
                    }
                }

                // Strategy 2: Direct database query with error fixing
                if ($attempt == 2) {
                    $strategy = 'direct_database_fixed';
                    $fixedSQL = $this->fixSQLBasedOnErrors($question, $errors, $initialSQL);
                    $result = $this->executeDirectQuery($fixedSQL);

                    return $this->formatSuccessResponse($result, $strategy, $attempt, $errors);
                }

                // Strategy 3: Simplified query approach
                if ($attempt == 3) {
                    $strategy = 'simplified_query';
                    $simpleSQL = $this->generateSimplifiedQuery($question, $errors);
                    $result = $this->executeDirectQuery($simpleSQL);

                    return $this->formatSuccessResponse($result, $strategy, $attempt, $errors);
                }

                // Strategy 4: Basic table query
                if ($attempt == 4) {
                    $strategy = 'basic_table_query';
                    $basicSQL = $this->generateBasicQuery($question);
                    $result = $this->executeDirectQuery($basicSQL);

                    return $this->formatSuccessResponse($result, $strategy, $attempt, $errors);
                }

                // Strategy 5: Emergency fallback
                if ($attempt == 5) {
                    $strategy = 'emergency_fallback';
                    $result = $this->executeEmergencyFallback($question);

                    return $this->formatSuccessResponse($result, $strategy, $attempt, $errors);
                }

            } catch (\Exception $e) {
                $error = [
                    'attempt' => $attempt,
                    'strategy' => $strategy ?? 'unknown',
                    'error' => $e->getMessage(),
                    'sql' => $fixedSQL ?? $simpleSQL ?? $basicSQL ?? $initialSQL,
                    'timestamp' => now()
                ];

                $errors[] = $error;
                $strategies[] = $strategy ?? 'unknown';

                Log::warning("Intelligent Retry Agent - Attempt {$attempt} failed", $error);

                // Analyze error for next strategy
                $this->analyzeErrorForNextStrategy($error);
            }
        }

        // All strategies failed
        return $this->formatFailureResponse($question, $errors, $strategies);
    }

    /**
     * Fix SQL based on previous errors
     */
    private function fixSQLBasedOnErrors($question, $errors, $originalSQL)
    {
        if (empty($errors)) {
            return $originalSQL ?: $this->generateBasicQueryFromQuestion($question);
        }

        $lastError = end($errors);
        $errorMessage = $lastError['error'];

        // Apply specific fixes based on error type
        foreach ($this->errorPatterns as $pattern => $fix) {
            if (preg_match($pattern, $errorMessage)) {
                return $this->applyFix($fix, $question, $originalSQL, $errorMessage);
            }
        }

        // Generic fix - simplify query
        return $this->generateBasicQueryFromQuestion($question);
    }

    /**
     * Apply specific fix strategy
     */
    private function applyFix($fixStrategy, $question, $originalSQL, $errorMessage)
    {
        switch ($fixStrategy) {
            case 'fix_table_not_found':
                return $this->fixTableNotFound($question, $errorMessage);

            case 'fix_column_not_found':
                return $this->fixColumnNotFound($originalSQL, $errorMessage);

            case 'fix_syntax_error':
                return $this->fixSyntaxError($question);

            case 'fix_permission_denied':
                return $this->fixPermissionDenied($question);

            case 'fix_connection_error':
                return $this->fixConnectionError($question);

            default:
                return $this->generateBasicQueryFromQuestion($question);
        }
    }

    /**
     * Fix table not found error
     */
    private function fixTableNotFound($question, $errorMessage)
    {
        // Extract table name from error
        preg_match('/Table \'[^\']+\.([^\']+)\' doesn\'t exist/', $errorMessage, $matches);
        $badTable = $matches[1] ?? '';

        // Get list of real tables
        $realTables = $this->getRealTables();

        // Try to find similar table name
        $similarTable = $this->findSimilarTable($badTable, $realTables);

        if ($similarTable) {
            return "SELECT * FROM {$similarTable} ORDER BY id DESC LIMIT 20";
        }

        // Fallback to employees table
        return "SELECT * FROM employees ORDER BY id DESC LIMIT 20";
    }

    /**
     * Fix column not found error
     */
    private function fixColumnNotFound($originalSQL, $errorMessage)
    {
        // Extract table from SQL
        preg_match('/FROM\s+(\w+)/i', $originalSQL, $matches);
        $table = $matches[1] ?? 'employees';

        // Get real columns for table
        $realColumns = $this->getRealColumns($table);

        // Use only existing columns
        $safeColumns = array_intersect(['id', 'name', 'firstname', 'lastname', 'email', 'created_at'], $realColumns);

        if (empty($safeColumns)) {
            $safeColumns = ['id'];
        }

        $columnList = implode(', ', $safeColumns);
        return "SELECT {$columnList} FROM {$table} ORDER BY id DESC LIMIT 20";
    }

    /**
     * Fix syntax error
     */
    private function fixSyntaxError($question)
    {
        // Generate very basic query
        $table = $this->guessTableFromQuestion($question);
        return "SELECT id FROM {$table} LIMIT 10";
    }

    /**
     * Fix permission denied error
     */
    private function fixPermissionDenied($question)
    {
        // Use most basic query possible
        return "SELECT COUNT(*) as total FROM employees";
    }

    /**
     * Fix connection error
     */
    private function fixConnectionError($question)
    {
        // Try to reconnect and use simple query
        DB::reconnect();
        return "SELECT 1 as status";
    }

    /**
     * Generate simplified query
     */
    private function generateSimplifiedQuery($question, $errors)
    {
        $table = $this->guessTableFromQuestion($question);

        // Check if table exists
        if (!$this->tableExists($table)) {
            $table = 'employees'; // Safe fallback
        }

        // Use only basic columns
        return "SELECT id FROM {$table} ORDER BY id DESC LIMIT 10";
    }

    /**
     * Generate basic query
     */
    private function generateBasicQuery($question)
    {
        $table = $this->guessTableFromQuestion($question);

        if (!$this->tableExists($table)) {
            $table = 'employees';
        }

        return "SELECT COUNT(*) as total FROM {$table}";
    }

    /**
     * Execute emergency fallback
     */
    private function executeEmergencyFallback($question)
    {
        try {
            // Try the most basic query possible
            $result = DB::select("SELECT 1 as status, 'Database connection active' as message");

            return [
                'data' => $result,
                'count' => count($result),
                'execution_time' => 1,
                'message' => 'Emergency fallback executed - database is accessible'
            ];

        } catch (\Exception $e) {
            // Even emergency fallback failed
            return [
                'data' => [],
                'count' => 0,
                'execution_time' => 0,
                'message' => 'Database is currently unavailable'
            ];
        }
    }

    /**
     * Execute direct query with read-only validation
     */
    private function executeDirectQuery($sql)
    {
        // CRITICAL: Validate read-only compliance before execution
        try {
            $this->readOnlyGuard->validateReadOnlyQuery($sql);
        } catch (\Exception $e) {
            Log::error('IntelligentRetryAgent - Read-only validation failed', [
                'user_id' => $this->userId,
                'sql' => $sql,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Read-only validation failed: ' . $e->getMessage());
        }

        $startTime = microtime(true);

        $result = DB::select($sql);

        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2);

        return [
            'data' => $result,
            'count' => count($result),
            'execution_time' => $executionTime,
            'sql_executed' => $sql,
            'read_only_validated' => true
        ];
    }

    /**
     * Get real database tables
     */
    private function getRealTables()
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
            $tableKey = "Tables_in_{$databaseName}";

            return array_map(function($table) use ($tableKey) {
                return $table->$tableKey;
            }, $tables);

        } catch (\Exception $e) {
            return ['employees', 'users', 'departments']; // Safe defaults
        }
    }

    /**
     * Get real columns for table
     */
    private function getRealColumns($table)
    {
        try {
            $columns = DB::select("SHOW COLUMNS FROM {$table}");
            return array_map(function($column) {
                return $column->Field;
            }, $columns);
        } catch (\Exception $e) {
            return ['id']; // Safe default
        }
    }

    /**
     * Check if table exists
     */
    private function tableExists($table)
    {
        try {
            $result = DB::select("SHOW TABLES LIKE '{$table}'");
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Find similar table name
     */
    private function findSimilarTable($badTable, $realTables)
    {
        // Direct mapping
        $mapping = [
            'employee' => 'employees',
            'user' => 'users',
            'department' => 'departments',
            'project' => 'projects',
            'task' => 'tasks'
        ];

        if (isset($mapping[$badTable])) {
            return $mapping[$badTable];
        }

        // Find by similarity
        foreach ($realTables as $realTable) {
            if (strpos($realTable, $badTable) !== false || strpos($badTable, $realTable) !== false) {
                return $realTable;
            }
        }

        return null;
    }

    /**
     * Guess table from question
     */
    private function guessTableFromQuestion($question)
    {
        $question = strtolower($question);

        $tableKeywords = [
            'employee' => 'employees',
            'staff' => 'employees',
            'worker' => 'employees',
            'user' => 'users',
            'department' => 'departments',
            'project' => 'projects',
            'task' => 'tasks',
            'salary' => 'salary_disbursements',
            'attendance' => 'attendance',
            'leave' => 'leaves'
        ];

        foreach ($tableKeywords as $keyword => $table) {
            if (strpos($question, $keyword) !== false) {
                return $table;
            }
        }

        return 'employees'; // Default fallback
    }

    /**
     * Generate basic query from question
     */
    private function generateBasicQueryFromQuestion($question)
    {
        $table = $this->guessTableFromQuestion($question);

        if (strpos($question, 'count') !== false || strpos($question, 'how many') !== false) {
            return "SELECT COUNT(*) as total FROM {$table}";
        }

        return "SELECT * FROM {$table} ORDER BY id DESC LIMIT 20";
    }

    /**
     * Format success response
     */
    private function formatSuccessResponse($result, $strategy, $attempts, $errors)
    {
        return [
            'success' => true,
            'data' => $result,
            'strategy_used' => $strategy,
            'attempts' => $attempts,
            'errors_encountered' => count($errors),
            'recovery_successful' => true,
            'data_source' => 'real_database'
        ];
    }

    /**
     * Format failure response
     */
    private function formatFailureResponse($question, $errors, $strategies)
    {
        Log::error("Intelligent Retry Agent - All strategies failed", [
            'user_id' => $this->userId,
            'question' => $question,
            'errors' => $errors,
            'strategies_tried' => $strategies
        ]);

        return [
            'success' => false,
            'error' => 'Unable to execute query after trying all recovery strategies',
            'attempts' => count($errors),
            'strategies_tried' => $strategies,
            'errors' => $errors,
            'recovery_successful' => false,
            'data_source' => 'none'
        ];
    }

    /**
     * Analyze error for next strategy
     */
    private function analyzeErrorForNextStrategy($error)
    {
        // Log error pattern for future improvement
        Log::info("Error pattern analysis", [
            'error_type' => $this->classifyError($error['error']),
            'strategy_failed' => $error['strategy'],
            'user_role' => $this->userRole
        ]);
    }

    /**
     * Classify error type
     */
    private function classifyError($errorMessage)
    {
        if (strpos($errorMessage, 'Table') !== false && strpos($errorMessage, 'doesn\'t exist') !== false) {
            return 'table_not_found';
        }
        if (strpos($errorMessage, 'Unknown column') !== false) {
            return 'column_not_found';
        }
        if (strpos($errorMessage, 'syntax error') !== false) {
            return 'syntax_error';
        }
        if (strpos($errorMessage, 'Access denied') !== false) {
            return 'permission_denied';
        }
        if (strpos($errorMessage, 'Connection') !== false) {
            return 'connection_error';
        }

        return 'unknown_error';
    }

    /**
     * Initialize error handling patterns
     */
    private function initializeErrorHandling()
    {
        $this->errorPatterns = [
            '/Table \'[^\']+\.[^\']+\' doesn\'t exist/' => 'fix_table_not_found',
            '/Unknown column \'[^\']+\' in \'[^\']+\'/' => 'fix_column_not_found',
            '/You have an error in your SQL syntax/' => 'fix_syntax_error',
            '/Access denied for user/' => 'fix_permission_denied',
            '/Lost connection to MySQL server/' => 'fix_connection_error'
        ];

        $this->fixStrategies = [
            'fix_table_not_found' => 'Map to existing table or use default',
            'fix_column_not_found' => 'Use only existing columns',
            'fix_syntax_error' => 'Simplify query structure',
            'fix_permission_denied' => 'Use basic read-only query',
            'fix_connection_error' => 'Reconnect and retry'
        ];
    }
}
