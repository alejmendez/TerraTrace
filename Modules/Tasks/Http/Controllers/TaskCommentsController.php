<?php

namespace Modules\Tasks\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Core\Http\Controllers\Controller;
use Modules\Core\Traits\HasPermissionMiddleware;
use Modules\Tasks\Http\Requests\StoreTaskCommentRequest;
use Modules\Tasks\Http\Requests\UpdateTaskCommentRequest;
use Modules\Tasks\Http\Resources\TaskCommentResource;
use Modules\Tasks\Models\TaskComment;
use Modules\Tasks\Services\TaskCommentService;

class TaskCommentsController extends Controller
{
    use HasPermissionMiddleware;

    public function __construct(private readonly TaskCommentService $taskComments)
    {
        $this->setupPermissionMiddleware();
    }

    public function store(StoreTaskCommentRequest $request)
    {
        $data = $request->validated();
        $taskComment = $this->taskComments->create($data, auth()->user());

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
            throw new AuthorizationException(
                'No estás autorizado para actualizar este comentario.'
            );
        }

        $taskComment = $this->taskComments->update($taskComment, $data);

        return response()->json([
            'data' => new TaskCommentResource($taskComment),
        ]);
    }

    /**
     * Remove the specified resource in storage.
     */
    public function destroy(string $id)
    {
        $this->taskComments->delete($id);

        return response()->noContent();
    }
}
