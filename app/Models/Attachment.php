<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'original_name',
        'stored_name',
        'mime_type',
        'size',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Human-readable file size.
     * 1024 → "1 KB", 1048576 → "1 MB"
     */
    public function formattedSize(): string
    {
        $bytes = $this->size;

        if ($bytes < 1024)       return "{$bytes} B";
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    /**
     * Get the full storage URL for download.
     */
    public function url(): string
    {
        return Storage::url($this->stored_name);
    }
}
