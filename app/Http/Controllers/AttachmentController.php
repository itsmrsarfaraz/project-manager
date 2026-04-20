<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    /**
     * Upload a file and attach it to a task.
     */
    public function store(Request $request, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('addTask', $project); // any member can upload

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240',          // max 10MB (in kilobytes)
                'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,gif,zip,txt',
                // Whitelist approach: only allow specific types
                // NEVER use 'mimes:*' or skip this rule
            ],
        ]);

        $uploadedFile = $request->file('file');

        // Generate a unique stored filename to prevent collisions and path traversal
        // Format: tasks/{task_id}/{uuid}.{extension}
        $storedName = 'tasks/' . $task->id . '/' .
            Str::uuid() . '.' .
            $uploadedFile->getClientOriginalExtension();

        // Store the file — returns the path
        Storage::disk('public')->putFileAs(
            'tasks/' . $task->id,
            $uploadedFile,
            basename($storedName)
        );

        $task->attachments()->create([
            'user_id'       => Auth::id(),
            'original_name' => $uploadedFile->getClientOriginalName(),
            'stored_name'   => $storedName,
            'mime_type'     => $uploadedFile->getMimeType(),
            'size'          => $uploadedFile->getSize(),
        ]);

        return back()->with('success', 'File uploaded successfully.');
    }

    /**
     * Download a file.
     */
    public function show(Project $project, Task $task, Attachment $attachment): mixed
    {
        $this->authorize('view', $task);

        // Verify the attachment belongs to this task (prevent IDOR)
        abort_if($attachment->task_id !== $task->id, 404);

        // ✅ After — cast to the concrete cloud filesystem which has download()
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->download(
            $attachment->stored_name,
            $attachment->original_name  // download with the original filename
        );
    }

    /**
     * Delete a file.
     */
    public function destroy(Project $project, Task $task, Attachment $attachment): RedirectResponse
    {
        // Only the uploader or a manager/owner can delete
        if ($attachment->user_id !== Auth::id()) {
            $this->authorize('update', $project);
        }

        // Delete from disk first, then database
        Storage::disk('public')->delete($attachment->stored_name);

        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }
}
