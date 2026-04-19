<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $this->user()->can('update', $task);
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'status' => [
                'required',
                'string',
                'in:todo,in_progress,done',
            ],
            'priority' => [
                'required',
                'string',
                'in:low,medium,high',
            ],
            'due_date' => [
                'nullable',
                'date',
                // No 'after_or_equal:today' on update — editing an old task
                // shouldn't force you to change the due date
            ],
            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The task must have a title.',
            'title.min'      => 'Task title must be at least 3 characters.',
        ];
    }

    /**
     * Same after-hook as StoreTaskRequest —
     * assigned user must be a project member.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $assignedTo = $this->input('assigned_to');
            $project    = $this->route('project');

            if ($assignedTo && $project) {
                $isMember = $project->members()
                    ->where('user_id', $assignedTo)
                    ->exists();

                if (! $isMember) {
                    $validator->errors()->add(
                        'assigned_to',
                        'The assigned user must be a member of this project.'
                    );
                }
            }
        });
    }
}
