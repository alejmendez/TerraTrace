<?php

namespace Modules\Fields\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FieldResource extends JsonResource
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
            'location' => $this->location,
            'size' => $this->size,
            'plants_count' => $this->plants_count,
            'quarters_count' => $this->quarters_count,
            'blueprint' => $this->blueprint ? Storage::url($this->blueprint) : '',
            'documents' => $this->documents ? $this->documents->map(fn ($document) => [
                'id' => $document->id,
                'name' => $document->name,
                'url' => Storage::url($document->path),
            ]) : [],
            'owner' => [
                'dni' => $this->owner_dni,
                'name' => $this->owner_name,
            ],
        ];
    }
}
