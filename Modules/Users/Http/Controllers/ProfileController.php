<?php

namespace Modules\Users\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Services\ListEntity;
use Modules\Users\Http\Requests\ProfileUpdateRequest;
use Modules\Users\Http\Resources\UserResource;
use Modules\Users\Services\UserService;

class ProfileController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function edit(Request $request): Response
    {
        return Inertia::render('Users::Profile/Edit', [
            'data' => new UserResource($request->user()),
            'roles' => ListEntity::call('role'),
            'toast' => session('toast'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['avatar'] = $this->storeAvatar($request);
        $this->userService->update($request->user()->id, $data);

        return redirect()->route('profile.edit')->with('toast', [
            'severity' => 'success',
            'summary' => __('generics.messages.saved_successfully'),
            'detail' => __('generics.messages.saved_successfully'),
            'life' => 5000,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $this->userService->delete($user->id);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function storeAvatar(ProfileUpdateRequest $request)
    {
        if ($request->file('avatar') == null) {
            return null;
        }

        return $request->file('avatar')->storePublicly('public/avatars');
    }
}
