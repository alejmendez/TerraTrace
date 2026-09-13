<?php

namespace Modules\Fields\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DogResource extends JsonResource
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
            'breed' => $this->breed,
            'gender' => $this->gender,
            'birthdate' => $this->birthdate,
            'age' => $this->age,
            'veterinary' => $this->veterinary,
            'avatar' => $this->avatar ? Storage::url($this->avatar) : '',
            'field' => [
                'id' => optional($this->field)->id,
                'name' => optional($this->field)->name,
            ],
            'couple' => [
                'id' => optional($this->couple)->id,
                'name' => optional($this->couple)->full_name,
            ],
            'vaccines' => $this->vaccines->map(function ($v) {
                return [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'date' => $v['date'],
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
