<?php

namespace Modules\Dashboard\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Dashboard\Services\Dashboard;
use Modules\Fields\Http\Resources\FieldResource;

class DashboardController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(private Dashboard $dashboard)
    {
        $this->setupPermissionMiddleware();
    }

    public function index()
    {
        $field_id = request('field_id');
        $data = $this->dashboard->show($field_id);

        if ($data['field']) {
            $data['field'] = new FieldResource($data['field']);
        }

        return Inertia::render('Dashboard::Index', $data);
    }
}
