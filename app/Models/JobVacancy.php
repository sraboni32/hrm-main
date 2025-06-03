<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'title',
        'description',
        'link',
        'status',
        'created_by',
        'company_id'
    ];

    protected $casts = [
        'company_id' => 'integer',
        'created_by' => 'integer',
        'status' => 'boolean'
    ];

    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
} 