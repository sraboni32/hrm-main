<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusAllowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'amount', 'type', 'description'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
} 