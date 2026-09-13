<?php

namespace Modules\Tasks\Http\Controllers;

use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Tasks\Http\Requests\StoreTaskCommentRequest;
use Modules\Tasks\Http\Requests\UpdateTaskCommentRequest;
use Modules\Tasks\Http\Resources\TaskCommentResource;
use Modules\Tasks\Models\TaskComment;
use Modules\Tasks\Services\CreateTaskComment;
use Modules\Tasks\Services\DeleteTaskComment;
use Modules\Tasks\Services\UpdateTaskComment;

class TaskCommentsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct()
    {
        $this->setupPermissionMiddleware();
    }

    public function store(StoreTaskCommentRequest $request)
    {
        $data = $request->validated();
        $taskComment = CreateTaskComment::call($data, auth()->user());

        return response()->json([
            'data' => new TaskCommentResource($taskComment),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskCommentRequest $request, string $id)
    {
        $data = $request->validated();
        $taskComment = TaskComment::findOrFail($id);

        // Verificar si el usuario actual es el propietario del comentario
        if ($taskComment->user_id !== auth()->id()) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'No estás autorizado para actualizar este comentario.'
            );
        }

        $taskComment = UpdateTaskComment::call($taskComment, $data);

        return response()->json([
            'data' => new TaskCommentResource($taskComment),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DeleteTaskComment::call($id);

        return response()->noContent();
    }
}
