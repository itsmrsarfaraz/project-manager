<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()->can('addMember', $project);
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'exists:users,email', // user must be registered
            ],
            'role' => [
                'required',
                'string',
                'in:manager,member', // can't invite someone as owner
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'No account was found with that email address.',
            'email.required' => 'Please enter an email address.',
            'role.in'      => 'Role must be either manager or member.',
        ];
    }

    /**
     * After-hook: prevent inviting someone who is already a member.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $project = $this->route('project');
            $email   = $this->input('email');

            if (! $email || ! $project) {
                return;
            }

            $user = \App\Models\User::where('email', $email)->first();

            if (! $user) {
                return; // already caught by exists:users,email rule
            }

            $alreadyMember = $project->members()
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyMember) {
                $validator->errors()->add(
                    'email',
                    'This user is already a member of the project.'
                );
            }
        });
    }
}
