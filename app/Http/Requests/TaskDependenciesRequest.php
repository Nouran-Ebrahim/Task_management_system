<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskDependenciesRequest extends BaseRequest
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
        return [
            'depends_on_task_id' => 'required|array|min:1',
            'depends_on_task_id.*' => 'required|exists:tasks,id'
        ];
    }

    public function messages(): array
    {
        return [
            'depends_on_task_id.required' => 'Please provide the task dependencies.',
            'depends_on_task_id.array' => 'The task dependencies must be a list.',
            'depends_on_task_id.min' => 'You must provide at least one task dependency.',
            'depends_on_task_id.*.exists' => 'The selected task does not exist.',
            'depends_on_task_id.*.required' => 'The dependency task is required.',
        ];
    }
}
