// In app/Models/Employee.php
public function tasks()
{
    return $this->hasMany(Task::class, 'employee_id');
}
 
// In app/Models/Task.php
public function employee()
{
    return $this->belongsTo(Employee::class, 'employee_id');
} 