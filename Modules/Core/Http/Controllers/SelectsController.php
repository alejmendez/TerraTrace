<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Services\ListEntities;
use Modules\Core\Services\ListEntity;
use Modules\Core\Traits\HasPermissionMiddleware;

class SelectsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct()
    {
        $this->setupPermissionMiddleware();
    }

    public function index()
    {
        $entity = request('entity');
        $filter = request('filter', []);

        if ($entity === 'multiple') {
            $entities = json_decode(request('entities', '{}'));

            return response()->json(ListEntities::call($entities));
        }

        return response()->json(ListEntity::call($entity, $filter));
    }
}
