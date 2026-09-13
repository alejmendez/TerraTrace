<?php

namespace Modules\Fields\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ToolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'purchase_date' => $this->purchase_date,
            'last_maintenance' => $this->last_maintenance,
            'purchase_location' => $this->purchase_location,
            'type' => $this->type,
            'contact' => $this->contact,
            'note' => $this->note,
        ];
    }
}
