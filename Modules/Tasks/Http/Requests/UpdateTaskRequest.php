<?php

namespace Modules\Tasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'status' => 'required',
            'status.value' => 'required|string',
            'priority' => 'required',
            'priority.value' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'field_id' => 'required',
            'field_id.value' => 'required|exists:fields,id',
            'quarter_id' => '',
            'quarter_ids.*.value' => 'nullable|integer|exists:quarters,id',
            'plant_id' => '',
            'plant_id.*.value' => 'nullable|integer|exists:plants,id',
            'rows' => '',
            'rows.*.value' => 'nullable|string',
            'responsible_id' => 'required',
            'responsible_id.value' => 'required|exists:users,id',
            // Additional validation rules for related tables
            'tools' => 'nullable|array',
            'tools.*.value' => 'exists:tools,id',
            'security_equipments' => 'nullable|array',
            'security_equipments.*.value' => 'exists:security_equipments,id',
            'machineries' => 'nullable|array',
            'machineries.*.value' => 'exists:machineries,id',
            'supplies' => 'nullable|array',
            'supplies.*.name' => 'nullable|string|max:255',
            'supplies.*.brand' => 'nullable|string|max:255',
            'supplies.*.quantity' => 'nullable|numeric|min:0',
            'supplies.*.unit' => 'nullable',
            'supplies.*.unit.value' => 'string',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nombre',
            'status' => 'estado',
            'repeat_number' => 'número de repetición',
            'repeat_type' => 'tipo de repetición',
            'priority' => 'prioridad',
            'start_date' => 'fecha inicio',
            'end_date' => 'fecha fin',
            'field_id' => 'campo',
            'quarter_id' => 'cuartel',
            'plant_id' => 'planta',
            'responsible_id' => 'responsable',
            'comments' => 'comentarios',
            'tools' => 'herramientas',
            'security_equipments' => 'equipos de seguridad',
            'machineries' => 'maquinarias',
            'supplies.*.name' => 'suministros.*.nombre',
            'supplies.*.brand' => 'suministros.*.marca',
            'supplies.*.quantity' => 'suministros.*.cantidad',
            'supplies.*.unit' => 'suministros.*.unidad',
        ];
    }
}
