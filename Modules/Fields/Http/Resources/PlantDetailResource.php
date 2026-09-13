<?php

namespace Modules\Fields\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PlantDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $value = $this->value;
        if (str_ends_with($this->type, '_photo')) {
            $value = Storage::url($this->value);
        }

        return [
            'id' => $this->id,
            'type' => $this->type,
            'value' => $value,
            'note' => $this->note,
            'plant_code' => $this->plant->code,
            'is_active' => $this->is_active,
            'updated_at' => $this->updated_at,
        ];
    }
}
