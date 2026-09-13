<?php

namespace Modules\Fields\Services\Quarters;

use Modules\Fields\Models\Quarter;

class UpdateQuarter
{
    public static function call($id, $data): Quarter
    {
        $quarter = Quarter::findOrFail($id);

        $quarter->name = $data['name'];
        $quarter->area = $data['area'];
        $quarter->field_id = $data['field_id']['value'];
        $quarter->responsible_id = $data['responsible_id']['value'];

        if ($data['blueprint']) {
            $quarter->blueprint = $data['blueprint'];
        }

        if ($data['blueprintRemove'] === '1') {
            $quarter->blueprint = null;
        }

        $quarter->save();

        return $quarter;
    }
}
