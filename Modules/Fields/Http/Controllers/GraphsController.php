<?php

namespace Modules\Fields\Http\Controllers;

use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Services\GraphDataService;

class GraphsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(private readonly GraphDataService $graphs)
    {
        $this->setupPermissionMiddleware();
    }

    public function index()
    {
        return $this->graphs->dispatch(
            request('id'),
            request('year'),
            request('type', ''),
            request('filters', '')
        );
    }
}
