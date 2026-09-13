<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\ForbiddenController;
use Modules\Core\Http\Controllers\NotificationsController;
use Modules\Core\Http\Controllers\SelectsController;
use Modules\Core\Http\Controllers\StartController;
use Modules\Core\Services\CacheService;

Route::get('/', function () {
    if (Auth::check()) {
        $userPermissions = CacheService::getUserPermissions(Auth::user());
        if ($userPermissions->contains('dashboard.index')) {
            return redirect()->route('dashboard.index');
        }

        return redirect()->route('start.index');
    }

    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/start', [StartController::class, 'index'])->name('start.index');
    Route::get('/select/{entity}', [SelectsController::class, 'index'])->name('selects.index');
    Route::get('/select/multiple', [SelectsController::class, 'multiple'])->name('selects.multiple');

    Route::get('/notifications/{type}/unread', [NotificationsController::class, 'unread'])->name('notifications.unread');

    Route::get('/forbidden', [ForbiddenController::class, 'forbidden'])->name('forbidden');
});
