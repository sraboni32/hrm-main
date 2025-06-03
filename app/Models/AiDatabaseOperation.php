<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiDatabaseOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_role',
        'original_question',
        'generated_sql',
        'operation_type',
        'query_analysis',
        'result_summary',
        'affected_rows',
        'result_count',
        'success',
        'error_message',
        'execution_time'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'query_analysis' => 'array',
        'result_summary' => 'array',
        'affected_rows' => 'integer',
        'result_count' => 'integer',
        'success' => 'boolean',
        'execution_time' => 'decimal:3'
    ];

    /**
     * Get the user that performed the operation
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for successful operations
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope for failed operations
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope for specific operation types
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('operation_type', $type);
    }

    /**
     * Scope for specific user role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('user_role', $role);
    }

    /**
     * Get operations for today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get operations for this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Get operations for this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * Get formatted execution time
     */
    public function getFormattedExecutionTimeAttribute()
    {
        if ($this->execution_time) {
            return number_format($this->execution_time, 3) . 's';
        }
        return 'N/A';
    }

    /**
     * Get operation summary
     */
    public function getSummaryAttribute()
    {
        $summary = "User {$this->user->username} ({$this->user_role}) executed {$this->operation_type} query";
        
        if ($this->success) {
            if ($this->operation_type === 'select' && $this->result_count) {
                $summary .= " returning {$this->result_count} records";
            } elseif (in_array($this->operation_type, ['update', 'delete']) && $this->affected_rows) {
                $summary .= " affecting {$this->affected_rows} rows";
            } elseif ($this->operation_type === 'insert') {
                $summary .= " successfully";
            }
        } else {
            $summary .= " but failed: {$this->error_message}";
        }

        return $summary;
    }

    /**
     * Check if operation is dangerous
     */
    public function isDangerous()
    {
        $dangerousPatterns = [
            'DELETE',
            'UPDATE.*SET',
            'DROP',
            'TRUNCATE',
            'ALTER'
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match("/{$pattern}/i", $this->generated_sql)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get risk level
     */
    public function getRiskLevelAttribute()
    {
        if (!$this->success) {
            return 'failed';
        }

        if ($this->isDangerous()) {
            return 'high';
        }

        if ($this->operation_type === 'select') {
            return 'low';
        }

        return 'medium';
    }
}
