<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $taskId = $this->route('task'); // Get the task ID from route parameter

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255', 'unique:tasks,title,' . $taskId],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:pending,in_progress,completed'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required',
            'title.max' => 'Task title cannot exceed 255 characters',
            'title.unique' => 'A task with this title already exists',
            'status.in' => 'Status must be one of: pending, in_progress, completed',
            'priority.in' => 'Priority must be one of: low, medium, high',
            'due_date.date' => 'Due date must be a valid date',
            'due_date.after_or_equal' => 'Due date cannot be in the past',
        ];
    }
}
