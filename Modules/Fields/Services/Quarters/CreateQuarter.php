<?php

namespace Modules\Fields\Services\Quarters;

use Modules\Fields\Models\Quarter;

class CreateQuarter
{
    public static function call($data): Quarter
    {
        $quarter = new Quarter;

        $quarter->name = $data['name'];
        $quarter->area = $data['area'];
        $quarter->field_id = $data['field_id']['value'];
        $quarter->responsible_id = $data['responsible_id']['value'];
        $quarter->blueprint = $data['blueprint'];

        $quarter->save();

        return $quarter;
    }
}
