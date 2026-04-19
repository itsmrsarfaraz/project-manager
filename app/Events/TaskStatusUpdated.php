<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// ShouldBroadcast → this event is sent over WebSocket
class TaskStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Task $task,
        public User $updatedBy
    ) {}

    /**
     * The channel to broadcast on.
     * PrivateChannel requires authentication (our channel closure above).
     * All project members listening on this channel will receive this event.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("project.{$this->task->project_id}"),
        ];
    }

    /**
     * The event name the JavaScript client listens for.
     */
    public function broadcastAs(): string
    {
        return 'task.status.updated';
    }

    /**
     * The data sent to the client.
     * Keep this minimal — only what the JS needs to update the UI.
     */
    public function broadcastWith(): array
    {
        return [
            'task_id'    => $this->task->id,
            'title'      => $this->task->title,
            'status'     => $this->task->status,
            'updated_by' => $this->updatedBy->name,
        ];
    }
}
