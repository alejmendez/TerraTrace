<?php

namespace Modules\Fields\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkPlantRequest extends FormRequest
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
            'field_id' => 'required',
            'field_id.value' => 'required|exists:fields,id',
            'quarter_id' => 'required',
            'quarter_id.value' => 'required|exists:quarters,id',
            'bulk_file' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'field_id' => 'campo',
            'quarter_id' => 'cuartel',
            'bulk_file' => 'archivo',
        ];
    }
}
