<?php

namespace Modules\Users\Http\Controllers;

use Inertia\Inertia;
use Modules\Auth\Services\AuthService;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Users\Http\Requests\StoreUserRequest;
use Modules\Users\Http\Requests\UpdateUserRequest;
use Modules\Users\Http\Resources\UserResource;
use Modules\Users\Services\UserService;

class UsersController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(
        private readonly UserService $userService,
        private readonly AuthService $auth,
    ) {
        $this->setupPermissionMiddleware();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payload = $this->userService->collection(request()->all());

        return Inertia::render('Users::List', [
            'toast' => session('toast'),
            'records' => $payload['items'],
            'meta' => $payload['meta'],
            'summary' => $payload['summary'],
            'roles' => $this->auth->roles(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Users::Create', [
            'roles' => $this->auth->roles(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['avatar'] = $this->storeAvatar($request);
        $this->userService->create($data);

        return redirect()->route('users.index')->with('toast', [
            'severity' => 'success',
            'summary' => __('generics.messages.saved_successfully'),
            'detail' => __('generics.messages.saved_successfully'),
            'life' => 5000,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = $this->userService->find($id);
        $current_tab = request()->get('current_tab', 'file');

        return Inertia::render('Users::Show', [
            'data' => new UserResource($user),
            'current_tab' => $current_tab,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = $this->userService->find($id);

        return Inertia::render('Users::Edit', [
            'data' => new UserResource($user),
            'roles' => $this->auth->roles(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        $data = $request->validated();
        $data['avatar'] = $this->storeAvatar($request);
        $this->userService->update($id, $data);

        return redirect()->route('users.index')->with('toast', [
            'severity' => 'success',
            'summary' => __('generics.messages.saved_successfully'),
            'detail' => __('generics.messages.saved_successfully'),
            'life' => 5000,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->userService->delete($id);

        return response()->noContent();
    }

    protected function storeAvatar(UpdateUserRequest|StoreUserRequest $request)
    {
        if ($request->file('avatar') == null) {
            return null;
        }

        return $request->file('avatar')->storePublicly('avatars', ['disk' => 'public']);
    }
}
