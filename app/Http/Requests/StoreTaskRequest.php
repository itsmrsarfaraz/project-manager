<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()->can('addTask', $project);
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
                'after_or_equal:today',
            ],
            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
                // Advanced: assigned user must be a member of the project
                // We'll add this via an after-hook below
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'        => 'The task must have a title.',
            'title.min'             => 'Task title must be at least 3 characters.',
            'due_date.after_or_equal' => 'The due date cannot be in the past.',
            'assigned_to.exists'    => 'The selected user does not exist.',
            'status.in'             => 'Invalid status value.',
            'priority.in'           => 'Invalid priority value.',
        ];
    }

    public function attributes(): array
    {
        return [
            'assigned_to' => 'assigned user',
            'due_date'    => 'due date',
        ];
    }

    /**
     * Add extra validation AFTER the basic rules pass.
     *
     * This is called an "after validation hook".
     * Use it for rules that require database lookups or
     * complex logic that can't be expressed as a simple rule.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $assignedTo = $this->input('assigned_to');
            $project    = $this->route('project');

            if ($assignedTo && $project) {
                // Check the assigned user is actually a member of this project
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
