<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Authorization is handled here using the Policy.
     * $this->route('project') gets the bound Project model from the URL.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        // Delegate to the ProjectPolicy::update() method
        return $this->user()->can('update', $project);
    }

    public function rules(): array
    {
        return [
            'name' => [
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
                'in:active,archived,completed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please give your project a name.',
            'name.min'      => 'Project name must be at least 3 characters.',
            'status.in'     => 'Status must be active, archived, or completed.',
        ];
    }
}
