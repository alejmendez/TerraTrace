<?php

namespace Modules\Tasks\Services;

use Modules\Core\Models\Notification;
use Modules\Tasks\Models\TaskComment;

class DeleteTaskComment
{
    public static function call(string $id): void
    {
        $taskComment = TaskComment::findOrFail($id);

        // Verificar si el usuario actual es el propietario del comentario
        if ($taskComment->user_id !== auth()->id()) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'No estás autorizado para eliminar este comentario.'
            );
        }

        Notification::whereRaw("data like '%\"task_comment_id\":$id,%'")->delete();
        $taskComment->delete();
    }
}
