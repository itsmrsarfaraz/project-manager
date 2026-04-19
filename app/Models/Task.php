<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'assigned_to',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date', // automatically cast to Carbon date object
    ];

    // Which project does this task belong to?
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Which user is assigned to this task?
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * All comments on this task.
     * MorphMany = "I have many of these polymorphic things"
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->latest(); // newest first
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class)->latest();
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class);
    }

    public function scopeSearch($query, ?string $term): void
    {
        if (blank($term)) {
            return;
        }

        $term = trim($term);

        $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
                ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    public function scopeFilterStatus($query, ?string $status): void
    {
        $allowed = ['todo', 'in_progress', 'done'];

        if (blank($status) || ! in_array($status, $allowed)) {
            return;
        }

        $query->where('status', $status);
    }

    public function scopeFilterPriority($query, ?string $priority): void
    {
        $allowed = ['low', 'medium', 'high'];

        if (blank($priority) || ! in_array($priority, $allowed)) {
            return;
        }

        $query->where('priority', $priority);
    }
}
