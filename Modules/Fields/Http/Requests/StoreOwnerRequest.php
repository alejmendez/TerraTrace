<?php

namespace Modules\Fields\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOwnerRequest extends FormRequest
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
            'name' => 'required|min:3|max:80',
            'dni' => ['required', 'regex:/^\d?\d\.\d{3}\.\d{3}\-[\d|k|K]$/', Rule::unique('owners')],
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Nombre',
            'dni' => 'RUT / ID',
        ];
    }
}
