<?php

namespace Modules\Tasks\Services;

use Modules\Tasks\Models\Task;
use Modules\Tasks\Notifications\TaskNotification;
use Modules\Users\Models\User;

class NotifyTaskComment
{
    public static function call(Task $task, ?string $taskComment, User $commentator, ?int $taskCommentId = null)
    {
        if (! $taskComment) {
            return;
        }

        $userIds = self::get_user_ids_from_comment($taskComment);
        // $userIds[] = $task->responsible_id;
        $users = User::whereIn('id', array_unique($userIds))->get();

        // "task_comment_id":21,

        foreach ($users as $user) {
            $user->notify(new TaskNotification([
                'task_id' => $task->id,
                'task_name' => $task->name,
                'task_comment_id' => $taskCommentId,
                'task_comment' => strip_tags($taskComment),
                'notifier_user_id' => $commentator->id,
            ]));
        }
    }

    protected static function get_user_ids_from_comment($comment)
    {
        preg_match_all('/<span class="mention"[^>]*data-id="(\d+)"[^>]*>/', $comment, $matches);

        return $matches[1];
    }
}
