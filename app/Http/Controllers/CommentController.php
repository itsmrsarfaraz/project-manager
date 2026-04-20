<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a new comment on a task.
     */
    public function store(Request $request, Project $project, Task $task): RedirectResponse
    {
        // Any project member can comment
        $this->authorize('addTask', $project); // reuse — any member passes

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        // The polymorphic magic happens here:
        // Eloquent automatically sets commentable_type and commentable_id
        $task->comments()->create([
            'user_id' => Auth::id(),
            'body'    => $validated['body'],
        ]);

        return back()->with('success', 'Comment added.');
    }

    /**
     * Delete a comment.
     */
    public function destroy(Project $project, Task $task, Comment $comment): RedirectResponse
    {
        // Only the comment author or project owner can delete
        if ($comment->user_id !== Auth::id()) {
            $this->authorize('delete', $project); // owner check
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
