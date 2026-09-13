<?php

namespace Modules\Fields\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDogRequest extends FormRequest
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
            'breed' => 'required|max:80',
            'gender' => 'required',
            'gender.value' => [
                'required',
                Rule::in(['M', 'F']),
            ],
            'birthdate' => 'required|date',
            'age' => 'required',
            'field_id' => 'required',
            'field_id.value' => 'required|exists:fields,id',
            'veterinary' => 'required|max:80',
            'couple_id' => 'required',
            'couple_id.value' => 'required|exists:users,id',
            'avatar' => '',
            'avatarRemove' => 'boolean',
            'vaccines.*.id' => 'nullable',
            'vaccines.*.name' => 'max:80|nullable',
            'vaccines.*.date' => 'date|nullable',
            'vaccines.*.code' => 'max:80|nullable',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nombres',
            'breed' => 'raza',
            'gender' => 'género',
            'age' => 'edad',
            'birthdate' => 'fecha de nacimiento',
            'field_id' => 'campo',
            'veterinary' => 'veterinario',
            'couple_id' => 'pareja',
            'avatar' => 'foto',
            'vaccines.*.name' => 'nombre de vacuna',
            'vaccines.*.date' => 'fecha de vacunación',
            'vaccines.*.code' => 'código',
        ];
    }
}
