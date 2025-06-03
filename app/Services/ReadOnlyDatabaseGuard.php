<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\AiDatabaseOperation;

/**
 * Read-Only Database Guard - Prevents All Write Operations
 * 
 * Features:
 * - Blocks all write operations (INSERT, UPDATE, DELETE, ALTER, DROP, etc.)
 * - Allows only safe read operations (SELECT, SHOW, DESCRIBE, EXPLAIN)
 * - Comprehensive SQL analysis and validation
 * - Detailed logging of blocked attempts
 * - Multiple validation layers for security
 */
class ReadOnlyDatabaseGuard
{
    private $userId;
    private $userRole;
    private $allowedOperations;
    private $blockedOperations;
    private $dangerousPatterns;

    public function __construct($userId, $userRole)
    {
        $this->userId = $userId;
        $this->userRole = $userRole;
        $this->initializeSecurityRules();
    }

    /**
     * Validate SQL query for read-only compliance
     */
    public function validateReadOnlyQuery($sqlQuery, $originalQuestion = null)
    {
        try {
            // Normalize SQL for analysis
            $normalizedSQL = $this->normalizeSQL($sqlQuery);
            
            // Multiple validation layers
            $validations = [
                'operation_type' => $this->validateOperationType($normalizedSQL),
                'dangerous_patterns' => $this->validateDangerousPatterns($normalizedSQL),
                'write_keywords' => $this->validateWriteKeywords($normalizedSQL),
                'function_calls' => $this->validateFunctionCalls($normalizedSQL),
                'system_operations' => $this->validateSystemOperations($normalizedSQL)
            ];

            // Check each validation
            foreach ($validations as $validationType => $result) {
                if (!$result['valid']) {
                    $this->logBlockedAttempt($sqlQuery, $originalQuestion, $validationType, $result['reason']);
                    
                    throw new \Exception(
                        "Read-only violation detected ({$validationType}): {$result['reason']}"
                    );
                }
            }

            // Log successful validation
            $this->logSuccessfulValidation($sqlQuery, $originalQuestion);

            return [
                'valid' => true,
                'sql_query' => $sqlQuery,
                'validation_passed' => true,
                'access_level' => 'read_only_approved'
            ];

        } catch (\Exception $e) {
            Log::error('Read-Only Database Guard - Validation failed', [
                'user_id' => $this->userId,
                'sql_query' => $sqlQuery,
                'original_question' => $originalQuestion,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Validate operation type (must be read-only)
     */
    private function validateOperationType($sql)
    {
        // Extract the main operation
        $operation = $this->extractMainOperation($sql);
        
        if (in_array($operation, $this->allowedOperations)) {
            return [
                'valid' => true,
                'operation' => $operation
            ];
        }

        if (in_array($operation, $this->blockedOperations)) {
            return [
                'valid' => false,
                'reason' => "Write operation '{$operation}' is not allowed in read-only mode",
                'operation' => $operation
            ];
        }

        // Unknown operation - block by default
        return [
            'valid' => false,
            'reason' => "Unknown operation '{$operation}' is not allowed",
            'operation' => $operation
        ];
    }

    /**
     * Validate against dangerous patterns
     */
    private function validateDangerousPatterns($sql)
    {
        foreach ($this->dangerousPatterns as $pattern => $description) {
            if (preg_match($pattern, $sql)) {
                return [
                    'valid' => false,
                    'reason' => "Dangerous pattern detected: {$description}",
                    'pattern' => $pattern
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Validate against write keywords
     */
    private function validateWriteKeywords($sql)
    {
        $writeKeywords = [
            'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER', 'TRUNCATE',
            'REPLACE', 'MERGE', 'UPSERT', 'GRANT', 'REVOKE', 'COMMIT', 'ROLLBACK',
            'START TRANSACTION', 'BEGIN', 'SAVEPOINT', 'RELEASE', 'LOCK', 'UNLOCK'
        ];

        foreach ($writeKeywords as $keyword) {
            if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $sql)) {
                return [
                    'valid' => false,
                    'reason' => "Write keyword '{$keyword}' is not allowed in read-only mode",
                    'keyword' => $keyword
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Validate function calls (block dangerous functions)
     */
    private function validateFunctionCalls($sql)
    {
        $dangerousFunctions = [
            'LOAD_FILE', 'INTO OUTFILE', 'INTO DUMPFILE', 'LOAD DATA',
            'BENCHMARK', 'SLEEP', 'GET_LOCK', 'RELEASE_LOCK',
            'USER', 'SYSTEM_USER', 'SESSION_USER', 'CONNECTION_ID'
        ];

        foreach ($dangerousFunctions as $function) {
            if (preg_match('/\b' . preg_quote($function, '/') . '\b/i', $sql)) {
                return [
                    'valid' => false,
                    'reason' => "Dangerous function '{$function}' is not allowed",
                    'function' => $function
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Validate system operations
     */
    private function validateSystemOperations($sql)
    {
        $systemOperations = [
            'SHOW PROCESSLIST', 'KILL', 'FLUSH', 'RESET', 'PURGE',
            'ANALYZE TABLE', 'OPTIMIZE TABLE', 'REPAIR TABLE', 'CHECK TABLE'
        ];

        foreach ($systemOperations as $operation) {
            if (preg_match('/\b' . preg_quote($operation, '/') . '\b/i', $sql)) {
                return [
                    'valid' => false,
                    'reason' => "System operation '{$operation}' is not allowed",
                    'operation' => $operation
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Extract main operation from SQL
     */
    private function extractMainOperation($sql)
    {
        // Remove comments and normalize whitespace
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        $sql = trim(preg_replace('/\s+/', ' ', $sql));

        // Extract first significant word
        if (preg_match('/^\s*(\w+)/i', $sql, $matches)) {
            return strtoupper($matches[1]);
        }

        return 'UNKNOWN';
    }

    /**
     * Normalize SQL for analysis
     */
    private function normalizeSQL($sql)
    {
        // Remove extra whitespace and normalize case for keywords
        $sql = preg_replace('/\s+/', ' ', trim($sql));
        
        // Convert to uppercase for keyword matching
        return strtoupper($sql);
    }

    /**
     * Generate safe read-only alternative query
     */
    public function generateSafeAlternative($blockedSQL, $originalQuestion)
    {
        try {
            Log::info('Read-Only Guard - Generating safe alternative', [
                'user_id' => $this->userId,
                'blocked_sql' => $blockedSQL,
                'original_question' => $originalQuestion
            ]);

            // Analyze what the user was trying to do
            $intent = $this->analyzeUserIntent($originalQuestion, $blockedSQL);
            
            // Generate safe read-only query based on intent
            $safeQuery = $this->generateSafeQuery($intent);
            
            // Validate the safe query
            $this->validateReadOnlyQuery($safeQuery, $originalQuestion);
            
            return [
                'safe_query' => $safeQuery,
                'intent' => $intent,
                'explanation' => $this->generateExplanation($intent, $blockedSQL)
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate safe alternative: ' . $e->getMessage());
            
            // Ultimate fallback - basic count query
            return [
                'safe_query' => 'SELECT COUNT(*) as total_records FROM employees WHERE deleted_at IS NULL',
                'intent' => 'basic_info',
                'explanation' => 'Showing basic database information instead of the requested operation'
            ];
        }
    }

    /**
     * Analyze user intent from blocked query
     */
    private function analyzeUserIntent($question, $blockedSQL)
    {
        $question = strtolower($question ?? '');
        $sql = strtolower($blockedSQL);

        // Determine what user was trying to accomplish
        if (strpos($sql, 'update') !== false || strpos($question, 'update') !== false) {
            return [
                'type' => 'update_attempt',
                'table' => $this->extractTableFromSQL($sql),
                'description' => 'User attempted to update data'
            ];
        }

        if (strpos($sql, 'insert') !== false || strpos($question, 'add') !== false) {
            return [
                'type' => 'insert_attempt',
                'table' => $this->extractTableFromSQL($sql),
                'description' => 'User attempted to insert data'
            ];
        }

        if (strpos($sql, 'delete') !== false || strpos($question, 'delete') !== false) {
            return [
                'type' => 'delete_attempt',
                'table' => $this->extractTableFromSQL($sql),
                'description' => 'User attempted to delete data'
            ];
        }

        return [
            'type' => 'unknown_write_attempt',
            'table' => $this->extractTableFromSQL($sql),
            'description' => 'User attempted a write operation'
        ];
    }

    /**
     * Generate safe read-only query based on intent
     */
    private function generateSafeQuery($intent)
    {
        $table = $intent['table'] ?: 'employees';
        
        // Ensure table exists and is safe
        $safeTables = ['employees', 'departments', 'projects', 'tasks', 'attendance', 'leaves'];
        if (!in_array($table, $safeTables)) {
            $table = 'employees';
        }

        switch ($intent['type']) {
            case 'update_attempt':
                return "SELECT * FROM {$table} ORDER BY updated_at DESC LIMIT 10";
                
            case 'insert_attempt':
                return "SELECT COUNT(*) as current_total FROM {$table}";
                
            case 'delete_attempt':
                return "SELECT COUNT(*) as total_records FROM {$table} WHERE deleted_at IS NULL";
                
            default:
                return "SELECT * FROM {$table} ORDER BY id DESC LIMIT 10";
        }
    }

    /**
     * Extract table name from SQL
     */
    private function extractTableFromSQL($sql)
    {
        // Try to extract table name from various SQL patterns
        $patterns = [
            '/FROM\s+(\w+)/i',
            '/UPDATE\s+(\w+)/i',
            '/INSERT\s+INTO\s+(\w+)/i',
            '/DELETE\s+FROM\s+(\w+)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $sql, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Generate explanation for blocked operation
     */
    private function generateExplanation($intent, $blockedSQL)
    {
        switch ($intent['type']) {
            case 'update_attempt':
                return 'Update operations are not allowed. Showing recent records from the table instead.';
                
            case 'insert_attempt':
                return 'Insert operations are not allowed. Showing current record count instead.';
                
            case 'delete_attempt':
                return 'Delete operations are not allowed. Showing current record count instead.';
                
            default:
                return 'Write operations are not allowed in read-only mode. Showing read-only data instead.';
        }
    }

    /**
     * Log blocked attempt for security monitoring
     */
    private function logBlockedAttempt($sqlQuery, $originalQuestion, $validationType, $reason)
    {
        // Log to application logs
        Log::warning('Read-Only Database Guard - Blocked write attempt', [
            'user_id' => $this->userId,
            'user_role' => $this->userRole,
            'sql_query' => $sqlQuery,
            'original_question' => $originalQuestion,
            'validation_type' => $validationType,
            'block_reason' => $reason,
            'timestamp' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        // Log to database for audit trail
        try {
            AiDatabaseOperation::create([
                'user_id' => $this->userId,
                'user_role' => $this->userRole,
                'original_question' => $originalQuestion,
                'generated_sql' => $sqlQuery,
                'operation_type' => 'blocked_write_attempt',
                'query_analysis' => [
                    'read_only_guard' => 'blocked',
                    'validation_type' => $validationType,
                    'block_reason' => $reason,
                    'security_level' => 'high_risk'
                ],
                'result_summary' => [
                    'success' => false,
                    'blocked' => true,
                    'security_violation' => true
                ],
                'success' => false,
                'error_message' => "Read-only violation: {$reason}"
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log blocked attempt to database: ' . $e->getMessage());
        }
    }

    /**
     * Log successful validation
     */
    private function logSuccessfulValidation($sqlQuery, $originalQuestion)
    {
        Log::info('Read-Only Database Guard - Query approved', [
            'user_id' => $this->userId,
            'sql_query' => substr($sqlQuery, 0, 200),
            'original_question' => $originalQuestion,
            'validation_status' => 'approved'
        ]);
    }

    /**
     * Initialize security rules and patterns
     */
    private function initializeSecurityRules()
    {
        // Allowed read-only operations
        $this->allowedOperations = [
            'SELECT', 'SHOW', 'DESCRIBE', 'DESC', 'EXPLAIN', 'WITH'
        ];

        // Blocked write operations
        $this->blockedOperations = [
            'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER', 'TRUNCATE',
            'REPLACE', 'MERGE', 'UPSERT', 'GRANT', 'REVOKE', 'COMMIT', 'ROLLBACK',
            'START', 'BEGIN', 'SAVEPOINT', 'RELEASE', 'LOCK', 'UNLOCK', 'FLUSH',
            'RESET', 'PURGE', 'ANALYZE', 'OPTIMIZE', 'REPAIR', 'CHECK', 'KILL'
        ];

        // Dangerous patterns to block
        $this->dangerousPatterns = [
            '/INTO\s+OUTFILE/i' => 'File output operations',
            '/INTO\s+DUMPFILE/i' => 'File dump operations',
            '/LOAD\s+DATA/i' => 'Data loading operations',
            '/LOAD_FILE\s*\(/i' => 'File reading functions',
            '/BENCHMARK\s*\(/i' => 'Benchmark functions',
            '/SLEEP\s*\(/i' => 'Sleep functions',
            '/\bUNION\b.*\bSELECT\b.*\bFROM\b.*\bINFORMATION_SCHEMA\b/i' => 'Information schema injection',
            '/\bOR\b.*\b1\s*=\s*1\b/i' => 'SQL injection patterns',
            '/\bAND\b.*\b1\s*=\s*1\b/i' => 'SQL injection patterns',
            '/;\s*(INSERT|UPDATE|DELETE|DROP|CREATE|ALTER)/i' => 'Multiple statement injection',
            '/\bEXEC\b|\bEXECUTE\b/i' => 'Execute statements',
            '/\bSP_\w+/i' => 'Stored procedure calls',
            '/\bXP_\w+/i' => 'Extended procedure calls'
        ];
    }
}
