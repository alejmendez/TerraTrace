<?php

namespace Modules\Tasks\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Core\Models\Notification;
use Modules\Tasks\Models\TaskComment;
use Modules\Users\Models\User;

class TaskCommentService
{
    public function create(array $data, User $user): TaskComment
    {
        $taskComment = new TaskComment;
        $taskComment->task_id = $data['task_id'];
        $taskComment->comment = $this->stripTrailingBreaks($data['comment']);
        $taskComment->user_id = $user->id;
        $taskComment->save();

        NotifyTaskComment::call($taskComment->task, $taskComment->comment, $user, $taskComment->id);

        return $taskComment;
    }

    public function update(TaskComment $taskComment, array $data): TaskComment
    {
        $taskComment->comment = $this->stripTrailingBreaks($data['comment']);
        $taskComment->save();

        $existingNotification = Notification::whereRaw("data like '%\"task_comment_id\":$taskComment->id,%'")
            ->whereNull('read_at')
            ->first();

        if ($existingNotification) {
            $payload = json_decode($existingNotification->data, true);
            $payload['task_comment_id'] = $taskComment->id;
            $payload['task_comment'] = $taskComment->comment;
            $existingNotification->data = json_encode($payload);
            $existingNotification->save();
        } else {
            NotifyTaskComment::call($taskComment->task, $taskComment->comment, auth()->user(), $taskComment->id);
        }

        return $taskComment;
    }

    public function delete(string $id): void
    {
        $taskComment = TaskComment::findOrFail($id);

        // Verificar si el usuario actual es el propietario del comentario
        if ($taskComment->user_id !== auth()->id()) {
            throw new AuthorizationException(
                'No estás autorizado para eliminar este comentario.'
            );
        }

        Notification::whereRaw("data like '%\"task_comment_id\":$id,%'")->delete();
        $taskComment->delete();
    }

    /**
     * Trim trailing empty <p><br></p> paragraphs that editors (e.g. PrimeVue
     * Editor) emit when the user opens and closes an empty line.
     */
    private function stripTrailingBreaks(string $comment): string
    {
        return preg_replace('/<p><br><\/p>(\s*<p><br><\/p>)*$/', '', $comment);
    }
}
