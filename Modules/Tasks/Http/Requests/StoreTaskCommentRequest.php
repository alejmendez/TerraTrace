<?php

namespace Modules\Tasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskCommentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'task_id' => ['required', 'exists:tasks,id'],
            'comment' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'task_id.required' => 'La tarea es requerida',
            'task_id.exists' => 'La tarea seleccionada no existe',
            'comment.required' => 'El comentario es requerido',
            'comment.max' => 'El comentario no puede exceder los 1000 caracteres',
        ];
    }
}
