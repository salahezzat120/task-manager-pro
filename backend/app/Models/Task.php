<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'assignee_id',
        'title',
        'description',
        'due_date',
        'priority',
        'is_completed',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user who created the task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the user assigned to the task.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Scope to filter tasks by assignee.
     */
    public function scopeForAssignee($query, $userId)
    {
        return $query->where('assignee_id', $userId);
    }

    /**
     * Scope to filter tasks by status.
     */
    public function scopeByStatus($query, $status)
    {
        switch ($status) {
            case 'done':
                return $query->where('is_completed', true);
            case 'missed':
                return $query->where('is_completed', false)
                           ->where('due_date', '<', now()->toDateString());
            case 'due-today':
                return $query->where('is_completed', false)
                           ->where('due_date', now()->toDateString());
            case 'upcoming':
                return $query->where('is_completed', false)
                           ->where('due_date', '>', now()->toDateString());
            default:
                return $query;
        }
    }

    /**
     * Scope to filter tasks by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to search tasks by title or description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}

