<?php

namespace Modules\Fields\Services\Importers;

use Modules\Fields\Models\Importer;

class FindImporter
{
    public static function call($id)
    {
        $type = Importer::findOrFail($id);

        return $type;
    }
}
