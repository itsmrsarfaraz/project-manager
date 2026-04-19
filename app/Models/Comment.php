<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'body'];

    /**
     * The polymorphic relationship.
     * "This comment belongs to some commentable thing."
     * Laravel reads commentable_type to know which model to load.
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user who wrote this comment.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
