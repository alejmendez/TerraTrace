<?php

namespace Modules\Fields\Services\Fields;

use Illuminate\Support\Facades\DB;
use Modules\Fields\Models\Field;
use Modules\Fields\Services\Owners\CreateOrUpdateOwner;

class UpdateField
{
    public static function call($id, $data): Field
    {
        DB::beginTransaction();

        try {
            $field = Field::findOrFail($id);

            $field->name = $data['name'];
            $field->location = $data['location'];
            $field->size = $data['size'];

            if (isset($data['owner_dni'])) {
                $owner = CreateOrUpdateOwner::call($data['owner_dni'], $data['owner_name']);
                $field->owner_id = $owner->id;
            }

            if ($data['blueprint']) {
                $field->blueprint = $data['blueprint'];
            }

            if ($data['blueprintRemove'] === '1') {
                $field->blueprint = null;
            }

            $field->save();

            if (isset($data['documentsRemove']) && is_array($data['documentsRemove'])) {
                $field->documents()->whereIn('id', $data['documentsRemove'])->delete();
            }

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
