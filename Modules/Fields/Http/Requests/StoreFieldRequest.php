<?php

namespace Modules\Fields\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFieldRequest extends FormRequest
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
            'location' => 'required|max:100',
            'size' => 'required|numeric|between:0,999',
            'owner_dni' => ['required', 'regex:/^\d?\d\.\d{3}\.\d{3}\-[\d|k|K]$/'],
            'owner_name' => 'required|max:80',
            'blueprint' => '',
            'blueprintRemove' => 'boolean',
            'documents' => '',
            'documentsRemove' => '',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nombre',
            'location' => 'ubicación',
            'size' => 'superficie',
            'owner_dni' => 'rut del propietario',
            'owner_name' => 'nombre del propietario',
            'blueprint' => 'foto',
            'documents' => 'documentos',
        ];
    }
}
