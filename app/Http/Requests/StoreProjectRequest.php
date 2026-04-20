<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Moving authorization here keeps the controller method clean.
     * Any authenticated user can create a project.
     */
    public function authorize(): bool
    {
        return $this->user() !== null; // must be logged in (middleware handles this too)
    }

    /**
     * Get the validation rules that apply to the request.
     */
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

    /**
     * Custom human-readable error messages.
     * Laravel's defaults are fine, but these are more user-friendly.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please give your project a name.',
            'name.min'      => 'Project name must be at least 3 characters.',
            'status.in'     => 'Status must be active, archived, or completed.',
        ];
    }

    /**
     * Custom attribute names used in error messages.
     * Changes "The name field is required" to "The project name field is required"
     */
    public function attributes(): array
    {
        return [
            'name' => 'project name',
        ];
    }
}
