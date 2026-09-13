<?php

namespace Modules\Fields\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class BatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $harvests = $this->harvests;
        $quarters = $harvests->count() > 0 ? $harvests->first()->quarters : null;
        $field_name = $quarters && $quarters->count() > 0 ? $quarters->first()->field->name : null;

        return [
            'id' => $this->id,
            'batch_number' => $this->batch_number,
            'delivery_date' => $this->delivery_date,
            'carrier' => $this->carrier ?? '',
            'field_name' => $field_name ?? '',
            'total_weight' => $harvests->sum('weight'),
            'current_weight' => $this->current_weight,
            'importer_name' => optional($this->importer)->name,
            'importer_id' => [
                'value' => optional($this->importer)->id,
                'text' => optional($this->importer)->name,
            ],
            'harvests_elements' => $harvests->map(function ($harvest) {
                return [
                    'id' => $harvest->id,
                    'date' => $harvest->date,
                    'weight' => $harvest->weight,
                    'batch' => trans('harvest.form.batch.renderText', [
                        'week' => $harvest->week,
                        'batch' => $harvest->batch,
                    ]),
                ];
            }),
            'harvests' => $harvests->pluck('id'),
        ];
    }
}
