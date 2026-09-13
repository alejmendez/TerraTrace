<?php

namespace Modules\Fields\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PlantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $quarter = optional($this->quarter);
        $field = optional($quarter->field);
        $responsible = optional($quarter->responsible);
        $plant_type = optional($this->plant_type);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'plant_type' => [
                'id' => $plant_type->id,
                'name' => $plant_type->name,
            ],
            'age' => $this->age,
            'planned_at' => $this->planned_at,
            'nursery_origin' => $this->nursery_origin,
            'code' => $this->code,
            'row' => $this->row,
            'blueprint' => $this->blueprint ? Storage::url($this->blueprint) : '',
            'field' => [
                'id' => $field->id,
                'name' => $field->name,
            ],
            'quarter' => [
                'id' => $quarter->id,
                'name' => $quarter->name,
            ],
            'responsible' => [
                'id' => $responsible->id,
                'name' => $responsible->full_name,
            ],
        ];
    }
}
