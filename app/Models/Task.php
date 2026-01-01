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
    public function dependsOnTasks()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id');
    }
    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i a', strtotime($value));
    }
    public function canBeCompleted()
    {
        return
            $this->dependencies()->
                where('status', '!=', TaskStatus::COMPLETED->value)
                ->count() == 0;
    }
    public function hasCirculation($dependancyId)
    {
        return $this->checkCirculation($dependancyId, $this->id);
    }
    public function checkCirculation($dependancyId, $targetTaskId)
    {
        if ($dependancyId == $targetTaskId) {
            return true; //has Circulation
        }
        $dependancies = Task::find($dependancyId)?->dependencies()->pluck('tasks.id');
        foreach ($dependancies ?? [] as $depnd_id) {
            if ($this->checkCirculation($depnd_id, $targetTaskId)) {
                return true;
            }

        }
        return false;
    }
    protected function scopeAvailable($query)
    {
        if (auth()->check()) {
            if (auth()->user()->isManager()) {
                return $query;
            } else {
                return $query->where('assigned_to', auth()->user()->id);

            }
        }

    }
}
