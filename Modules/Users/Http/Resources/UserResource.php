<?php

namespace Modules\Users\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roleData = [
            'id' => '',
            'slug' => '',
            'name' => '',
        ];
        if (count($this->roles)) {
            $roleData = [
                'id' => $this->roles[0]->id,
                'slug' => Str::slug($this->roles[0]->name),
                'name' => $this->roles[0]->name,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'dni' => $this->dni,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'avatar' => $this->avatar ? Storage::url($this->avatar) : '',
            'role' => $roleData,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
