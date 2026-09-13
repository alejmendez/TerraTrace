<?php

namespace Modules\Fields\Services\Importers;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Importer;

class ListImporter
{
    public static function call($params = [])
    {
        $searchableColumns = ['name', 'slug'];

        $query = Importer::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $plant_types = $datatable->of($query)->make();

        return $plant_types;
    }
}
