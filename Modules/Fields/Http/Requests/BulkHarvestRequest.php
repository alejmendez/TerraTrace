<?php

namespace Modules\Fields\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkHarvestRequest extends FormRequest
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
            'harvest_id' => 'required',
            'harvest_id.value' => 'required|exists:harvests,id',
            'bulk_file' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'harvest_id' => 'cosecha',
            'bulk_file' => 'archivo',
        ];
    }
}
