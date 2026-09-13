<?php

namespace Modules\Fields\Services\Fields;

use Modules\Fields\Models\Field;

class FindField
{
    public static function call($id)
    {
        $field = Field::select(
            'fields.id',
            'fields.name',
            'fields.location',
            'fields.size',
            'fields.blueprint',
            'owners.name as owner_name',
            'owners.dni as owner_dni',
        )
            ->withCount('plants')
            ->withCount('quarters')
            ->leftjoin('owners', 'fields.owner_id', '=', 'owners.id')
            ->findOrFail($id);

        return $field;
    }
}
