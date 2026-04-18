<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;
    // Only these fields can be mass-assigned (security: prevents mass assignment attacks)
    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'status',
    ];

    // The user who CREATED/OWNS this project (one-to-many inverse)
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // All members of this project (many-to-many through pivot)
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')       // include 'role' from the pivot table
            ->withTimestamps();        // include pivot created_at/updated_at
    }

    // All tasks in this project (one-to-many)
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Calculate the completion percentage of a project's tasks.
     * Returns 0 if there are no tasks (avoids division by zero).
     *
     * Usage: $project->completionPercentage()
     * Or in dashboard with withCount: use the counts directly
     */
    public function completionPercentage(): int
    {
        $total = $this->tasks()->count();

        if ($total === 0) {
            return 0;
        }

        $done = $this->tasks()->where('status', 'done')->count();

        return (int) round(($done / $total) * 100);
    }
}
