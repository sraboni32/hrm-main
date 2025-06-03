<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskLink extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'title', 'url', 'description', 'task_id'
    ];

    protected $casts = [
        'task_id' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo('App\Models\Task', 'task_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }
}
