<?php

namespace Modules\Tasks\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'correlative' => $this->correlative,
            'name' => $this->name,
            'repeat_number' => $this->repeat_number,
            'repeat_type' => $this->repeat_type,
            'status' => $this->status,
            'priority' => $this->priority,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'field' => [
                'value' => optional($this->field)->id,
                'text' => optional($this->field)->name,
            ],
            'quarters' => optional($this->quarters)->pluck('id'),
            'rows' => $this->rows,
            'plants' => optional($this->plants)->pluck('id'),
            'responsible_id' => optional($this->responsible)->id,
            'responsible_name' => optional($this->responsible)->full_name,
            'note' => $this->note,
            'comments' => optional($this->comments)->sortBy('created_at')->map(fn ($comment) => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'user_id' => $comment->user->id,
                'user_name' => $comment->user->full_name,
                'user_avatar' => $comment->user->avatar_url,
                'created_at' => $comment->created_at,
            ])->toArray(),
            // Additional validation rules for related tables
            'tools' => optional($this->tools)->map(fn ($tool) => [
                'value' => $tool->id,
                'text' => $tool->name,
            ]),
            'security_equipments' => optional($this->security_equipments)->map(fn ($security_equipment) => [
                'value' => $security_equipment->id,
                'text' => $security_equipment->name,
            ]),
            'machineries' => optional($this->machineries)->map(fn ($machinery) => [
                'value' => $machinery->id,
                'text' => $machinery->name,
            ]),
            'supplies' => optional($this->supplies)->map(fn ($supply) => [
                'id' => $supply->id,
                'name' => $supply->name,
                'brand' => $supply->brand,
                'quantity' => $supply->quantity,
                'unit' => $supply->unit,
            ]),
        ];
    }
}
