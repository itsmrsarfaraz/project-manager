<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Project;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // relationships

    // Projects this user OWNS
    public function ownedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    // Projects this user is a MEMBER of (many-to-many)
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    // Tasks assigned to this user
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    // Helper functions 

    /**
     * Get this user's role on a specific project.
     * Returns null if the user is not a member.
     */
    public function roleOn(Project $project): ?string
    {
        // Find this user's pivot row for the given project
        $membership = $this->projects()
            ->where('project_id', $project->id)
            ->first();

        return $membership?->pivot->role; // null-safe: returns null if not a member
    }

    /**
     * Check if the user is a member of a project (any role).
     */
    public function isMemberOf(Project $project): bool
    {
        return $this->projects()
            ->where('project_id', $project->id)
            ->exists(); // exists() is more efficient than count() > 0
    }

    // All comments written by this user
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
