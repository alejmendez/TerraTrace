<?php

namespace Modules\Fields\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlantDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'plant_code' => 'required|string|max:255|exists:plants,code',

            // Detalles de la planta
            'height' => 'nullable|numeric|min:0',
            'crown_diameter' => 'nullable|numeric|min:0',
            'invasion_radius' => 'nullable|numeric|min:0',
            'trunk_diameter' => 'nullable|numeric|min:0',
            'root_diameter' => 'nullable|numeric|min:0',

            'notes.height' => 'nullable',
            'notes.crown_diameter' => 'nullable',
            'notes.invasion_radius' => 'nullable',
            'notes.trunk_diameter' => 'nullable',
            'notes.root_diameter' => 'nullable',

            // Sanidad
            'foliage_sanitation' => 'nullable|string|max:255',
            'foliage_sanitation_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'trunk_sanitation' => 'nullable|string|max:255',
            'trunk_sanitation_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'soil_sanitation' => 'nullable|string|max:255',
            'soil_sanitation_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Otros
            'irrigation_system' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'plant_code.required' => 'El código de la planta es obligatorio.',
            'plant_code.exists' => 'El código de la planta no existe.',
            'foliage_sanitation_photo.image' => 'La foto de sanidad en el follaje debe ser una imagen.',
            'foliage_sanitation_photo.mimes' => 'La foto de sanidad en el follaje debe estar en formato jpg, jpeg o png.',
            'trunk_sanitation_photo.image' => 'La foto de sanidad en el tronco debe ser una imagen.',
            'trunk_sanitation_photo.mimes' => 'La foto de sanidad en el tronco debe estar en formato jpg, jpeg o png.',
            'soil_sanitation_photo.image' => 'La foto de sanidad del suelo debe ser una imagen.',
            'soil_sanitation_photo.mimes' => 'La foto de sanidad del suelo debe estar en formato jpg, jpeg o png.',
        ];
    }
}
