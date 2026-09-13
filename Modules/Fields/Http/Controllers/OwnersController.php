<?php

namespace Modules\Fields\Http\Controllers;

use Inertia\Inertia;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Fields\Http\Requests\StoreOwnerRequest;
use Modules\Fields\Http\Requests\UpdateOwnerRequest;
use Modules\Fields\Services\Owners\CreateOwner;
use Modules\Fields\Services\Owners\DeleteOwner;
use Modules\Fields\Services\Owners\ListOwner;
use Modules\Fields\Services\Owners\UpdateOwner;

class OwnersController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct()
    {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->exists('dt_params')) {
            $params = json_decode(request('dt_params', '[]'), true);

            return response()->json(ListOwner::call($params));
        }

        return Inertia::render('Fields::Owners/List');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOwnerRequest $request)
    {
        $data = $request->validated();
        CreateOwner::call($data);

        return [
            'success' => true,
            'message' => 'Owner created.',
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOwnerRequest $request, string $id)
    {
        $data = $request->validated();
        UpdateOwner::call($id, $data);

        return [
            'success' => true,
            'message' => 'Owner updated.',
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DeleteOwner::call($id);

        return response()->noContent();
    }
}
