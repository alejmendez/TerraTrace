<?php

namespace Modules\Fields\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHarvestRequest extends FormRequest
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
            'date' => 'required|date',
            'batch' => 'required|max:2',
            'dog_id' => 'required',
            'dog_id.value' => 'required|exists:dogs,id',
            'farmer_id' => 'required',
            'farmer_id.value' => 'required|exists:users,id',
            'assistant_id' => 'required',
            'assistant_id.value' => 'required|exists:users,id',
            'quarter_ids' => 'required',
            'quarter_ids.*.value' => 'integer',
            'details.*.id' => '',
            'details.*.plant_code' => 'exists:plants,code|nullable',
            'details.*.quality' => 'max:30|nullable',
            'details.*.weight' => 'numeric|between:0,99999|nullable',
            'weight' => 'numeric|between:0,99999',
            'note' => 'nullable|string|max:300',
        ];
    }

    public function attributes()
    {
        return [
            'date' => 'semana',
            'batch' => 'batch',
            'dog_id' => 'perro',
            'farmer_id' => 'agricultor',
            'assistant_id' => 'ayudante',
            'quarter_ids' => 'cuarteles',
            'weight' => 'peso total',
            'note' => 'nota',
        ];
    }
}
