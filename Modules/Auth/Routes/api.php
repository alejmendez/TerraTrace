<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\AuthenticatedApiController;

/*
|--------------------------------------------------------------------------
| Auth API Routes
|--------------------------------------------------------------------------
|
| Loaded by App\Providers\ModulesServiceProvider with the `api` middleware
| group and the standard `/api` URL prefix. Declare routes WITHOUT the
| `/api` prefix here — the loader applies it.
|
| Sanctum is the auth driver. `sign_in` is intentionally public: previously
| it carried the `guest` middleware (redirect-if-authenticated), which
| makes no sense for a JSON API and could confuse SPA/mobile clients.
|
*/

Route::post('auth/sign_in', [AuthenticatedApiController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/validate_token', [AuthenticatedApiController::class, 'validate_token']);
    Route::post('auth/sign_out', [AuthenticatedApiController::class, 'destroy']);
    Route::get('user', [AuthenticatedApiController::class, 'user']);
});
