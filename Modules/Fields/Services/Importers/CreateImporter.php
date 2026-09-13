<?php

namespace Modules\Fields\Services\Importers;

use Illuminate\Support\Str;
use Modules\Fields\Models\Importer;

class CreateImporter
{
    public static function call($data): Importer
    {
        $slug = Str::slug($data['name']);
        $importer = Importer::where('slug', $slug)->first();
        if (! $importer) {
            $importer = new Importer;
            $importer->name = $data['name'];
            $importer->slug = $slug;
            $importer->save();
        }

        return $importer;
    }
}
