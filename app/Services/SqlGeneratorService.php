<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SqlGeneratorService
{
    /**
     * Generate SELECT query
     */
    public function generateSelectQuery($question, $analysis, $schema)
    {
        $tables = $analysis['tables_involved'];
        $columns = $analysis['columns_mentioned'];
        $conditions = $analysis['conditions'];
        $aggregations = $analysis['aggregations'];

        // Default to employees table if none specified
        if (empty($tables)) {
            $tables = ['employees'];
        }

        $mainTable = $tables[0];
        $selectClause = $this->buildSelectClause($columns, $aggregations, $mainTable);
        $fromClause = $this->buildFromClause($tables);
        $whereClause = $this->buildWhereClause($conditions, $mainTable);
        $joinClause = $this->buildJoinClause($tables, $schema);

        $query = "SELECT {$selectClause} FROM {$fromClause}";

        if (!empty($joinClause)) {
            $query .= " {$joinClause}";
        }

        if (!empty($whereClause)) {
            $query .= " WHERE {$whereClause}";
        }

        // Add GROUP BY if aggregations are used
        if (!empty($aggregations) && !empty($columns)) {
            $groupColumns = implode(', ', array_slice($columns, 0, 2));
            if (!empty($groupColumns)) {
                $query .= " GROUP BY {$groupColumns}";
            }
        }

        // Add ORDER BY for better results
        $query .= " ORDER BY {$mainTable}.id DESC LIMIT 100";

        return $query;
    }

    /**
     * Generate UPDATE query
     */
    public function generateUpdateQuery($question, $analysis, $schema)
    {
        $tables = $analysis['tables_involved'];
        $conditions = $analysis['conditions'];

        if (empty($tables)) {
            throw new \Exception('Cannot determine table to update');
        }

        $table = $tables[0];

        // Extract update values from question
        $setClause = $this->extractUpdateValues($question);
        $whereClause = $this->buildWhereClause($conditions, $table);

        if (empty($setClause)) {
            throw new \Exception('Cannot determine what to update');
        }

        $query = "UPDATE {$table} SET {$setClause}";

        if (!empty($whereClause)) {
            $query .= " WHERE {$whereClause}";
        } else {
            throw new \Exception('UPDATE queries require WHERE conditions for safety');
        }

        return $query;
    }

    /**
     * Generate INSERT query
     */
    public function generateInsertQuery($question, $analysis, $schema)
    {
        $tables = $analysis['tables_involved'];

        if (empty($tables)) {
            throw new \Exception('Cannot determine table for insertion');
        }

        $table = $tables[0];

        // Extract insert values from question
        $insertData = $this->extractInsertValues($question, $table);

        if (empty($insertData)) {
            throw new \Exception('Cannot determine values to insert');
        }

        $columns = implode(', ', array_keys($insertData));
        $values = "'" . implode("', '", array_values($insertData)) . "'";

        return "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
    }

    /**
     * Generate DELETE query
     */
    public function generateDeleteQuery($question, $analysis, $schema)
    {
        $tables = $analysis['tables_involved'];
        $conditions = $analysis['conditions'];

        if (empty($tables)) {
            throw new \Exception('Cannot determine table for deletion');
        }

        $table = $tables[0];
        $whereClause = $this->buildWhereClause($conditions, $table);

        if (empty($whereClause)) {
            throw new \Exception('DELETE queries require WHERE conditions for safety');
        }

        return "DELETE FROM {$table} WHERE {$whereClause}";
    }

    /**
     * Build SELECT clause
     */
    private function buildSelectClause($columns, $aggregations, $mainTable)
    {
        if (!empty($aggregations)) {
            $selectParts = [];
            foreach ($aggregations as $agg) {
                if ($agg === 'COUNT') {
                    $selectParts[] = "COUNT(*) as total_count";
                } elseif (!empty($columns)) {
                    $column = $columns[0];
                    $selectParts[] = "{$agg}({$mainTable}.{$column}) as {$agg}_{$column}";
                }
            }

            // Add some basic columns for context
            if (!empty($columns)) {
                $selectParts = array_merge($selectParts, array_slice($columns, 0, 3));
            }

            return implode(', ', $selectParts);
        }

        if (!empty($columns)) {
            $selectColumns = [];
            foreach ($columns as $col) {
                // Check if column needs table prefix
                if (strpos($col, '.') === false) {
                    $selectColumns[] = "{$mainTable}.{$col}";
                } else {
                    $selectColumns[] = $col;
                }
            }
            return implode(', ', $selectColumns);
        }

        // Enhanced default select based on table type
        switch ($mainTable) {
            case 'employees':
                return "{$mainTable}.id, {$mainTable}.firstname, {$mainTable}.lastname, {$mainTable}.email, " .
                       "{$mainTable}.basic_salary, {$mainTable}.joining_date, " .
                       "CASE WHEN d.department_name IS NOT NULL THEN d.department_name " .
                       "     WHEN d.department IS NOT NULL THEN d.department " .
                       "     ELSE 'No Department' END as department_name, " .
                       "CASE WHEN des.designation IS NOT NULL THEN des.designation ELSE 'No Designation' END as designation_name";

            case 'projects':
                return "{$mainTable}.id, {$mainTable}.title, {$mainTable}.status, {$mainTable}.start_date, " .
                       "{$mainTable}.end_date, {$mainTable}.project_progress, {$mainTable}.budget, " .
                       "CASE WHEN c.firstname IS NOT NULL THEN CONCAT(c.firstname, ' ', c.lastname) ELSE 'No Client' END as client_name";

            case 'tasks':
                return "{$mainTable}.id, {$mainTable}.title, {$mainTable}.status, {$mainTable}.priority, " .
                       "{$mainTable}.start_date, {$mainTable}.end_date, " .
                       "CASE WHEN p.title IS NOT NULL THEN p.title ELSE 'No Project' END as project_name";

            case 'attendance':
                return "{$mainTable}.id, {$mainTable}.date, {$mainTable}.clock_in, {$mainTable}.clock_out, " .
                       "{$mainTable}.total_hours, {$mainTable}.status, " .
                       "CASE WHEN e.firstname IS NOT NULL THEN CONCAT(e.firstname, ' ', e.lastname) ELSE 'Unknown Employee' END as employee_name";

            case 'departments':
                return "{$mainTable}.id, " .
                       "CASE WHEN {$mainTable}.department_name IS NOT NULL THEN {$mainTable}.department_name " .
                       "     ELSE {$mainTable}.department END as department_name, " .
                       "CASE WHEN eh.firstname IS NOT NULL THEN CONCAT(eh.firstname, ' ', eh.lastname) ELSE 'No Head' END as head_name";

            default:
                return "{$mainTable}.*";
        }
    }

    /**
     * Build FROM clause
     */
    private function buildFromClause($tables)
    {
        return $tables[0];
    }

    /**
     * Build WHERE clause
     */
    private function buildWhereClause($conditions, $table)
    {
        $whereParts = [];

        foreach ($conditions as $key => $value) {
            switch ($key) {
                case 'year':
                    $whereParts[] = "YEAR({$table}.created_at) = {$value}";
                    break;
                case 'date_range':
                    $whereParts[] = $this->buildDateRangeCondition($value, $table);
                    break;
                case 'greater_than':
                    $whereParts[] = "{$table}.basic_salary > {$value}";
                    break;
                case 'less_than':
                    $whereParts[] = "{$table}.basic_salary < {$value}";
                    break;
            }
        }

        return implode(' AND ', $whereParts);
    }

    /**
     * Build JOIN clause
     */
    private function buildJoinClause($tables, $schema)
    {
        $joins = [];
        $mainTable = $tables[0];

        // Enhanced joins based on table relationships
        switch ($mainTable) {
            case 'employees':
                $joins[] = "LEFT JOIN departments d ON {$mainTable}.department_id = d.id";
                $joins[] = "LEFT JOIN designations des ON {$mainTable}.designation_id = des.id";
                $joins[] = "LEFT JOIN companies comp ON {$mainTable}.company_id = comp.id";
                $joins[] = "LEFT JOIN office_shifts os ON {$mainTable}.office_shift_id = os.id";
                $joins[] = "LEFT JOIN users u ON {$mainTable}.id = u.id";
                break;

            case 'projects':
                $joins[] = "LEFT JOIN clients c ON {$mainTable}.client_id = c.id";
                $joins[] = "LEFT JOIN companies comp ON {$mainTable}.company_id = comp.id";
                break;

            case 'tasks':
                $joins[] = "LEFT JOIN projects p ON {$mainTable}.project_id = p.id";
                $joins[] = "LEFT JOIN employees e ON {$mainTable}.assigned_to = e.id";
                break;

            case 'attendance':
                $joins[] = "LEFT JOIN employees e ON {$mainTable}.employee_id = e.id";
                $joins[] = "LEFT JOIN departments d ON e.department_id = d.id";
                break;

            case 'leaves':
                $joins[] = "LEFT JOIN employees e ON {$mainTable}.employee_id = e.id";
                $joins[] = "LEFT JOIN leave_types lt ON {$mainTable}.leave_type_id = lt.id";
                $joins[] = "LEFT JOIN departments d ON e.department_id = d.id";
                break;

            case 'salary_disbursements':
                $joins[] = "LEFT JOIN employees e ON {$mainTable}.employee_id = e.id";
                $joins[] = "LEFT JOIN departments d ON e.department_id = d.id";
                $joins[] = "LEFT JOIN designations des ON e.designation_id = des.id";
                break;

            case 'departments':
                $joins[] = "LEFT JOIN employees eh ON {$mainTable}.department_head = eh.id";
                $joins[] = "LEFT JOIN companies comp ON {$mainTable}.company_id = comp.id";
                break;

            case 'awards':
                $joins[] = "LEFT JOIN employees e ON {$mainTable}.employee_id = e.id";
                $joins[] = "LEFT JOIN award_types at ON {$mainTable}.award_type_id = at.id";
                $joins[] = "LEFT JOIN companies comp ON {$mainTable}.company_id = comp.id";
                break;

            case 'complaints':
                $joins[] = "LEFT JOIN employees ef ON {$mainTable}.employee_from = ef.id";
                $joins[] = "LEFT JOIN employees ea ON {$mainTable}.employee_against = ea.id";
                $joins[] = "LEFT JOIN companies comp ON {$mainTable}.company_id = comp.id";
                break;

            case 'trainings':
                $joins[] = "LEFT JOIN trainers tr ON {$mainTable}.trainer_id = tr.id";
                $joins[] = "LEFT JOIN training_skills ts ON {$mainTable}.training_skill_id = ts.id";
                $joins[] = "LEFT JOIN companies comp ON {$mainTable}.company_id = comp.id";
                break;
        }

        // Add joins for additional tables mentioned in the query
        for ($i = 1; $i < count($tables); $i++) {
            $table = $tables[$i];
            $join = $this->determineJoinCondition($mainTable, $table);
            if ($join && !in_array($join, $joins)) {
                $joins[] = $join;
            }
        }

        return implode(' ', $joins);
    }

    /**
     * Build date range condition
     */
    private function buildDateRangeCondition($range, $table)
    {
        switch ($range) {
            case 'this_month':
                return "MONTH({$table}.created_at) = MONTH(NOW()) AND YEAR({$table}.created_at) = YEAR(NOW())";
            case 'last_month':
                return "MONTH({$table}.created_at) = MONTH(NOW() - INTERVAL 1 MONTH) AND YEAR({$table}.created_at) = YEAR(NOW() - INTERVAL 1 MONTH)";
            case 'this_year':
                return "YEAR({$table}.created_at) = YEAR(NOW())";
            case 'last_year':
                return "YEAR({$table}.created_at) = YEAR(NOW()) - 1";
            default:
                return "1=1";
        }
    }

    /**
     * Determine JOIN condition between tables
     */
    private function determineJoinCondition($mainTable, $joinTable)
    {
        $joinMappings = [
            'employees' => [
                'departments' => "LEFT JOIN departments ON employees.department_id = departments.id",
                'designations' => "LEFT JOIN designations ON employees.designation_id = designations.id",
                'companies' => "LEFT JOIN companies ON employees.company_id = companies.id",
                'attendance' => "LEFT JOIN attendance ON employees.id = attendance.employee_id",
                'leaves' => "LEFT JOIN leaves ON employees.id = leaves.employee_id"
            ],
            'projects' => [
                'tasks' => "LEFT JOIN tasks ON projects.id = tasks.project_id",
                'clients' => "LEFT JOIN clients ON projects.client_id = clients.id"
            ],
            'tasks' => [
                'projects' => "LEFT JOIN projects ON tasks.project_id = projects.id"
            ]
        ];

        return $joinMappings[$mainTable][$joinTable] ?? null;
    }

    /**
     * Extract update values from question
     */
    private function extractUpdateValues($question)
    {
        $setParts = [];

        // Look for salary updates
        if (preg_match('/salary.*?(\d+)/', $question, $matches)) {
            $setParts[] = "basic_salary = {$matches[1]}";
        }

        // Look for status updates
        if (preg_match('/status.*?(active|inactive|pending|approved|rejected)/', $question, $matches)) {
            $setParts[] = "status = '{$matches[1]}'";
        }

        // Look for percentage increases
        if (preg_match('/increase.*?(\d+)%/', $question, $matches)) {
            $percentage = $matches[1];
            $setParts[] = "basic_salary = basic_salary * (1 + {$percentage}/100)";
        }

        return implode(', ', $setParts);
    }

    /**
     * Extract insert values from question
     */
    private function extractInsertValues($question, $table)
    {
        $data = [];

        // Extract common patterns for employee insertion
        if ($table === 'employees') {
            if (preg_match('/name.*?([A-Za-z]+)\s+([A-Za-z]+)/', $question, $matches)) {
                $data['firstname'] = $matches[1];
                $data['lastname'] = $matches[2];
            }

            if (preg_match('/email.*?([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $question, $matches)) {
                $data['email'] = $matches[1];
            }

            if (preg_match('/salary.*?(\d+)/', $question, $matches)) {
                $data['basic_salary'] = $matches[1];
            }
        }

        return $data;
    }

    /**
     * Generate fallback query when main generation fails
     */
    public function generateFallbackQuery($question, $analysis)
    {
        $tables = $analysis['tables_involved'];

        if (empty($tables)) {
            return "SELECT * FROM employees LIMIT 10";
        }

        $mainTable = $tables[0];
        return "SELECT * FROM {$mainTable} ORDER BY id DESC LIMIT 10";
    }

    /**
     * Get basic schema when full schema fails
     */
    public function getBasicSchema()
    {
        return [
            'tables' => [
                'employees' => [
                    'columns' => ['id', 'firstname', 'lastname', 'email', 'department_id', 'designation_id'],
                    'primary_key' => 'id',
                    'foreign_keys' => ['department_id', 'designation_id', 'company_id']
                ],
                'departments' => [
                    'columns' => ['id', 'department_name', 'department_head'],
                    'primary_key' => 'id',
                    'foreign_keys' => ['department_head']
                ]
            ]
        ];
    }

    /**
     * Generate count query
     */
    public function generateCountQuery($analysis, $schema)
    {
        $tables = $analysis['tables'];
        if (empty($tables)) {
            $tables = ['employees'];
        }

        $mainTable = $tables[0];
        $whereClause = $this->buildWhereClause($analysis['conditions'], $mainTable);
        $joinClause = $this->buildJoinClause($tables, $schema);

        $query = "SELECT COUNT(*) as total_count FROM {$mainTable}";

        if (!empty($joinClause)) {
            $query .= " {$joinClause}";
        }

        if (!empty($whereClause)) {
            $query .= " WHERE {$whereClause}";
        }

        return $query;
    }

    /**
     * Generate list query
     */
    public function generateListQuery($analysis, $schema)
    {
        return $this->generateSelectQuery('', $analysis, $schema);
    }

    /**
     * Generate find query
     */
    public function generateFindQuery($analysis, $schema)
    {
        return $this->generateSelectQuery('', $analysis, $schema);
    }

    /**
     * Generate aggregate query
     */
    public function generateAggregateQuery($analysis, $schema)
    {
        $tables = $analysis['tables'];
        if (empty($tables)) {
            $tables = ['employees'];
        }

        $mainTable = $tables[0];
        $aggregations = $analysis['aggregations'];

        if (empty($aggregations)) {
            // Detect aggregation from question
            $aggregations = ['COUNT'];
        }

        $selectClause = $this->buildSelectClause($analysis['columns'], $aggregations, $mainTable);
        $whereClause = $this->buildWhereClause($analysis['conditions'], $mainTable);
        $joinClause = $this->buildJoinClause($tables, $schema);

        $query = "SELECT {$selectClause} FROM {$mainTable}";

        if (!empty($joinClause)) {
            $query .= " {$joinClause}";
        }

        if (!empty($whereClause)) {
            $query .= " WHERE {$whereClause}";
        }

        // Add GROUP BY if needed
        if (!empty($analysis['columns']) && count($aggregations) > 0) {
            $groupColumns = implode(', ', array_slice($analysis['columns'], 0, 2));
            if (!empty($groupColumns)) {
                $query .= " GROUP BY {$groupColumns}";
            }
        }

        return $query;
    }

    /**
     * Generate default query
     */
    public function generateDefaultQuery($analysis, $schema)
    {
        return $this->generateSelectQuery('', $analysis, $schema);
    }

    /**
     * Extract time filters from question
     */
    public function extractTimeFilters($question)
    {
        $filters = [];

        if (strpos($question, 'today') !== false) {
            $filters['today'] = true;
        }

        if (strpos($question, 'this week') !== false) {
            $filters['this_week'] = true;
        }

        if (strpos($question, 'this month') !== false) {
            $filters['this_month'] = true;
        }

        if (strpos($question, 'this year') !== false) {
            $filters['this_year'] = true;
        }

        if (strpos($question, 'last month') !== false) {
            $filters['last_month'] = true;
        }

        if (strpos($question, 'last year') !== false) {
            $filters['last_year'] = true;
        }

        return $filters;
    }
}
