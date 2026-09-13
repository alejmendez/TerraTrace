<?php

namespace Modules\Fields\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuarterRequest extends FormRequest
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
            'name' => 'required|max:80',
            'area' => 'required|numeric|between:0,999',
            'field_id' => 'required',
            'field_id.value' => 'required|exists:fields,id',
            'responsible_id' => 'required',
            'responsible_id.value' => 'required|exists:users,id',
            'blueprint' => '',
            'blueprintRemove' => 'boolean',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nombre',
            'area' => 'superficie',
            'field_id' => 'campo',
            'responsible_id' => 'responsable',
            'blueprint' => 'foto',
        ];
    }
}
