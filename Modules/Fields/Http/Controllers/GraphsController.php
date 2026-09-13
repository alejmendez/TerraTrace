<?php

namespace Modules\Fields\Http\Controllers;

use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Services\Graphs\GraphData;

class GraphsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct()
    {
        $this->setupPermissionMiddleware();
    }

    public function index()
    {
        $id = request('id');
        $year = request('year');
        $type = request('type', '');
        $filters = request('filters', '');

        return GraphData::call($id, $year, $type, $filters);
    }
}
