<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Modules\Core\Http\Controllers\Controller;
use Modules\Users\Services\UserService;

class PasswordController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    /**
     * Update the user's password.
     *
     * `current_password` validation uses the standard Laravel rule so the
     * hash check stays in the validator. The actual update + cache
     * invalidation lives in UserService::updatePassword.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $this->userService->updatePassword($request->user(), $validated['password']);

        return back();
    }
}
