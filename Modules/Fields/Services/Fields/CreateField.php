<?php

namespace Modules\Fields\Services\Fields;

use Illuminate\Support\Facades\DB;
use Modules\Fields\Models\Field;
use Modules\Fields\Services\Owners\CreateOrUpdateOwner;

class CreateField
{
    public static function call($data): Field
    {
        DB::beginTransaction();

        try {
            $field = new Field;

            $field->name = $data['name'];
            $field->location = $data['location'];
            $field->size = $data['size'];
            $field->blueprint = $data['blueprint'];

            if (isset($data['owner_dni'])) {
                $owner = CreateOrUpdateOwner::call($data['owner_dni'], $data['owner_name']);
                $field->owner_id = $owner->id;
            }

            $field->save();

            if (isset($data['documents']) && is_array($data['documents'])) {
                foreach ($data['documents'] as $documentPath) {
                    $field->documents()->create([
                        'path' => $documentPath['path'],
                        'type' => $documentPath['type'],
                        'name' => $documentPath['name'],
                    ]);
                }
            }

            DB::commit();

            return $field;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
