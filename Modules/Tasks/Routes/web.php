<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Http\Controllers\TaskCommentsController;
use Modules\Tasks\Http\Controllers\TasksController;

Route::middleware('auth')->group(function () {
    Route::post('/task/comments', [TaskCommentsController::class, 'store'])->name('tasks.comments.store');
    Route::put('/task/comments/{taskComment}', [TaskCommentsController::class, 'update'])->name('tasks.comments.update');
    Route::delete('/task/comments/{taskComment}', [TaskCommentsController::class, 'destroy'])->name('tasks.comments.destroy');

    Route::resources([
        'tasks' => TasksController::class,
    ]);
});
