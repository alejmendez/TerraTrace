<?php

namespace Modules\Core\Services;

class ListEntities
{
    public static function call($entities)
    {
        $data = [];
        foreach ($entities as $entity => $filter) {
            $data[$entity] = ListEntity::call($entity, $filter);
        }

        return $data;
    }
}
