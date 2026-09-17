<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Services\CacheService;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     *
     * IMPORTANT: invalidate the user cache after the update. The cached
     * `CachedAuthUserProvider` entry (Modules\\Users model) lives for
     * 2 hours and would otherwise serve the OLD password hash until
     * it expires, breaking downstream `Hash::check()` on the cached
     * instance. `CacheService::clearUserCache()` also drops the user
     * data session, menu and unread-notifications caches.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        CacheService::clearUserCache($user);

        return back();
    }
}
