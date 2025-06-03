<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Vector Search Service - RAG Implementation for HR Database
 * 
 * Implements 2024 best practices:
 * - Vector embeddings for schema understanding
 * - Semantic similarity search
 * - RAG (Retrieval-Augmented Generation)
 * - Context-aware schema retrieval
 */
class VectorSearchService
{
    private $schemaVectors;
    private $queryVectors;
    private $embeddingDimension = 384; // Typical for sentence transformers

    public function __construct()
    {
        $this->initializeVectorStore();
    }

    /**
     * Find relevant schema components using vector similarity
     */
    public function findRelevantSchema($semanticAnalysis, $topK = 5)
    {
        // Generate query embedding
        $queryEmbedding = $this->generateQueryEmbedding($semanticAnalysis);
        
        // Find similar tables
        $relevantTables = $this->findSimilarTables($queryEmbedding, $topK);
        
        // Find similar columns
        $relevantColumns = $this->findSimilarColumns($queryEmbedding, $relevantTables, $topK);
        
        // Find relationships
        $relationships = $this->findRelevantRelationships($relevantTables);
        
        return [
            'tables' => $relevantTables,
            'columns' => $relevantColumns,
            'relationships' => $relationships,
            'similarity_scores' => $this->calculateSimilarityScores($queryEmbedding, $relevantTables)
        ];
    }

    /**
     * Generate embedding for semantic analysis
     */
    private function generateQueryEmbedding($semanticAnalysis)
    {
        // Create comprehensive query representation
        $queryText = $this->createQueryRepresentation($semanticAnalysis);
        
        // Generate embedding (simplified - in production use actual embedding service)
        return $this->generateEmbedding($queryText);
    }

    /**
     * Create text representation of query for embedding
     */
    private function createQueryRepresentation($semanticAnalysis)
    {
        $parts = [];
        
        // Add intent information
        $parts[] = "Intent: " . $semanticAnalysis['intent']['primary'];
        
        // Add entities
        if (!empty($semanticAnalysis['entities']['tables'])) {
            $parts[] = "Tables: " . implode(', ', $semanticAnalysis['entities']['tables']);
        }
        
        if (!empty($semanticAnalysis['entities']['columns'])) {
            $parts[] = "Columns: " . implode(', ', $semanticAnalysis['entities']['columns']);
        }
        
        // Add temporal context
        if ($semanticAnalysis['temporal_context']['period']) {
            $parts[] = "Time: " . $semanticAnalysis['temporal_context']['period'];
        }
        
        // Add aggregation type
        if (!empty($semanticAnalysis['aggregation_type'])) {
            $parts[] = "Aggregation: " . implode(', ', $semanticAnalysis['aggregation_type']);
        }
        
        // Add domain context
        if ($semanticAnalysis['domain_context']['hr_function']) {
            $parts[] = "HR Function: " . $semanticAnalysis['domain_context']['hr_function'];
        }
        
        return implode('. ', $parts);
    }

    /**
     * Find tables with highest similarity to query
     */
    private function findSimilarTables($queryEmbedding, $topK)
    {
        $similarities = [];
        
        foreach ($this->schemaVectors['tables'] as $tableName => $tableData) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $tableData['embedding']);
            $similarities[$tableName] = [
                'similarity' => $similarity,
                'metadata' => $tableData['metadata']
            ];
        }
        
        // Sort by similarity and return top K
        arsort($similarities);
        return array_slice($similarities, 0, $topK, true);
    }

    /**
     * Find columns relevant to the query and selected tables
     */
    private function findSimilarColumns($queryEmbedding, $relevantTables, $topK)
    {
        $columnSimilarities = [];
        
        // Only consider columns from relevant tables
        $tableNames = array_keys($relevantTables);
        
        foreach ($this->schemaVectors['columns'] as $columnKey => $columnData) {
            // Check if column belongs to relevant table
            $tableName = explode('.', $columnKey)[0];
            if (!in_array($tableName, $tableNames)) {
                continue;
            }
            
            $similarity = $this->cosineSimilarity($queryEmbedding, $columnData['embedding']);
            $columnSimilarities[$columnKey] = [
                'similarity' => $similarity,
                'metadata' => $columnData['metadata']
            ];
        }
        
        // Sort by similarity and return top K
        arsort($columnSimilarities);
        return array_slice($columnSimilarities, 0, $topK, true);
    }

    /**
     * Find relationships between relevant tables
     */
    private function findRelevantRelationships($relevantTables)
    {
        $relationships = [];
        $tableNames = array_keys($relevantTables);
        
        // Get stored relationships
        $allRelationships = $this->getTableRelationships();
        
        foreach ($allRelationships as $relationship) {
            $fromTable = $relationship['from_table'];
            $toTable = $relationship['to_table'];
            
            // Include relationship if both tables are relevant
            if (in_array($fromTable, $tableNames) && in_array($toTable, $tableNames)) {
                $relationships[] = $relationship;
            }
        }
        
        return $relationships;
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    private function cosineSimilarity($vector1, $vector2)
    {
        if (count($vector1) !== count($vector2)) {
            return 0;
        }
        
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;
        
        for ($i = 0; $i < count($vector1); $i++) {
            $dotProduct += $vector1[$i] * $vector2[$i];
            $magnitude1 += $vector1[$i] * $vector1[$i];
            $magnitude2 += $vector2[$i] * $vector2[$i];
        }
        
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);
        
        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }
        
        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Generate embedding vector (simplified implementation)
     */
    private function generateEmbedding($text)
    {
        // In production, this would call an actual embedding service
        // For now, create a simple hash-based embedding
        
        $embedding = array_fill(0, $this->embeddingDimension, 0);
        $words = explode(' ', strtolower($text));
        
        foreach ($words as $i => $word) {
            $hash = crc32($word);
            $index = abs($hash) % $this->embeddingDimension;
            $embedding[$index] += 1 / (1 + $i); // Weight by position
        }
        
        // Normalize
        $magnitude = sqrt(array_sum(array_map(function($x) { return $x * $x; }, $embedding)));
        if ($magnitude > 0) {
            $embedding = array_map(function($x) use ($magnitude) { return $x / $magnitude; }, $embedding);
        }
        
        return $embedding;
    }

    /**
     * Initialize vector store with schema embeddings
     */
    private function initializeVectorStore()
    {
        $this->schemaVectors = Cache::remember('schema_vectors', 3600, function() {
            return $this->generateSchemaVectors();
        });
    }

    /**
     * Generate vector embeddings for all schema components
     */
    private function generateSchemaVectors()
    {
        $vectors = [
            'tables' => [],
            'columns' => []
        ];
        
        // Generate table embeddings
        $tables = $this->getTableMetadata();
        foreach ($tables as $tableName => $tableInfo) {
            $description = $this->createTableDescription($tableName, $tableInfo);
            $vectors['tables'][$tableName] = [
                'embedding' => $this->generateEmbedding($description),
                'metadata' => $tableInfo
            ];
        }
        
        // Generate column embeddings
        $columns = $this->getColumnMetadata();
        foreach ($columns as $columnKey => $columnInfo) {
            $description = $this->createColumnDescription($columnKey, $columnInfo);
            $vectors['columns'][$columnKey] = [
                'embedding' => $this->generateEmbedding($description),
                'metadata' => $columnInfo
            ];
        }
        
        return $vectors;
    }

    /**
     * Get table metadata for embedding generation
     */
    private function getTableMetadata()
    {
        return [
            'employees' => [
                'description' => 'Employee personal and professional information including names, contact details, employment data, salary information',
                'business_context' => 'Human resources, staff management, personnel records',
                'common_queries' => ['employee list', 'staff information', 'personnel data']
            ],
            'departments' => [
                'description' => 'Organizational departments and divisions with hierarchy information',
                'business_context' => 'Organizational structure, department management',
                'common_queries' => ['department list', 'organizational structure', 'team information']
            ],
            'projects' => [
                'description' => 'Project information including timelines, budgets, status, and client relationships',
                'business_context' => 'Project management, client work, deliverables',
                'common_queries' => ['project status', 'project timeline', 'client projects']
            ],
            'tasks' => [
                'description' => 'Individual tasks and assignments with priorities, deadlines, and progress tracking',
                'business_context' => 'Task management, work assignments, productivity tracking',
                'common_queries' => ['task list', 'assignments', 'work progress']
            ],
            'attendance' => [
                'description' => 'Employee attendance records with clock-in/out times, breaks, and overtime',
                'business_context' => 'Time tracking, attendance monitoring, payroll calculation',
                'common_queries' => ['attendance records', 'time tracking', 'presence data']
            ],
            'leaves' => [
                'description' => 'Employee leave requests and approvals including vacation, sick leave, and other absences',
                'business_context' => 'Leave management, absence tracking, HR administration',
                'common_queries' => ['leave requests', 'vacation tracking', 'absence records']
            ],
            'salary_disbursements' => [
                'description' => 'Salary payments and disbursements with amounts, dates, and approval status',
                'business_context' => 'Payroll management, compensation tracking, financial records',
                'common_queries' => ['salary payments', 'payroll data', 'compensation records']
            ]
        ];
    }

    /**
     * Get column metadata for embedding generation
     */
    private function getColumnMetadata()
    {
        $columns = [];
        
        // Employee columns
        $employeeColumns = [
            'firstname' => 'Employee first name, given name',
            'lastname' => 'Employee last name, family name, surname',
            'email' => 'Employee email address, contact email',
            'basic_salary' => 'Employee base salary, monthly pay, compensation',
            'joining_date' => 'Employee start date, hire date, employment begin date',
            'department_id' => 'Employee department assignment, organizational unit'
        ];
        
        foreach ($employeeColumns as $column => $description) {
            $columns["employees.{$column}"] = [
                'description' => $description,
                'table' => 'employees',
                'data_type' => $this->getColumnDataType('employees', $column)
            ];
        }
        
        // Add other table columns...
        // (Simplified for brevity)
        
        return $columns;
    }

    /**
     * Create semantic description for table
     */
    private function createTableDescription($tableName, $tableInfo)
    {
        return "{$tableName} table: {$tableInfo['description']}. Business context: {$tableInfo['business_context']}. Common queries: " . implode(', ', $tableInfo['common_queries']);
    }

    /**
     * Create semantic description for column
     */
    private function createColumnDescription($columnKey, $columnInfo)
    {
        return "{$columnKey}: {$columnInfo['description']}. Data type: {$columnInfo['data_type']}";
    }

    /**
     * Get column data type
     */
    private function getColumnDataType($table, $column)
    {
        try {
            $result = DB::select("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
            return $result[0]->Type ?? 'unknown';
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    /**
     * Get table relationships
     */
    private function getTableRelationships()
    {
        return [
            [
                'from_table' => 'employees',
                'to_table' => 'departments',
                'from_column' => 'department_id',
                'to_column' => 'id',
                'relationship_type' => 'many_to_one'
            ],
            [
                'from_table' => 'employees',
                'to_table' => 'designations',
                'from_column' => 'designation_id',
                'to_column' => 'id',
                'relationship_type' => 'many_to_one'
            ],
            [
                'from_table' => 'tasks',
                'to_table' => 'projects',
                'from_column' => 'project_id',
                'to_column' => 'id',
                'relationship_type' => 'many_to_one'
            ],
            [
                'from_table' => 'attendance',
                'to_table' => 'employees',
                'from_column' => 'employee_id',
                'to_column' => 'id',
                'relationship_type' => 'many_to_one'
            ]
        ];
    }

    /**
     * Calculate similarity scores for debugging/monitoring
     */
    private function calculateSimilarityScores($queryEmbedding, $relevantTables)
    {
        $scores = [];
        foreach ($relevantTables as $tableName => $tableData) {
            $scores[$tableName] = $tableData['similarity'];
        }
        return $scores;
    }
}
