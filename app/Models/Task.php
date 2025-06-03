<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'title','project_id','summary','start_date','end_date','company_id',
        'description','estimated_hour','task_progress','status','note','priority',
        'quality_score','completed_at'
    ];

    protected $casts = [
        'project_id'  => 'integer',
        'company_id'  => 'integer',
        'quality_score' => 'double',
        'completed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo('App\Models\Project');
    }

    public function company()
    {
        return $this->hasOne('App\Models\Company', 'id', 'company_id');
    }

    public function assignedEmployees()
    {
        return $this->belongsToMany('App\Models\Employee');
    }

    public function links()
    {
        return $this->hasMany('App\Models\TaskLink');
    }

}
