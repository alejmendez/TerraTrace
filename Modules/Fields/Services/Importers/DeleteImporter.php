<?php

namespace Modules\Fields\Services\Importers;

use Modules\Fields\Models\Importer;

class DeleteImporter
{
    public static function call($id): void
    {
        Importer::destroy($id);
    }
}
