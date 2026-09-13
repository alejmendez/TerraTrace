<?php

namespace Modules\Fields\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class QuarterResource extends JsonResource
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
            'area' => $this->area,
            'plants_count' => $this->plants->count(),
            'blueprint' => $this->blueprint ? Storage::url($this->blueprint) : '',
            'field' => [
                'id' => optional($this->field)->id,
                'name' => optional($this->field)->name,
            ],
            'responsible' => [
                'id' => optional($this->responsible)->id,
                'name' => optional($this->responsible)->full_name,
            ],
        ];
    }
}
