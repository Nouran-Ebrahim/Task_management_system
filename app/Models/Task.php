<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'created_by',
        'assigned_to',
        'status'
    ];
    protected $casts = [
        'status' => TaskStatus::class,
    ];
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function createdby()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function dependencies()
    {
       return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id');
    }
    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i a', strtotime($value));
    }
}
