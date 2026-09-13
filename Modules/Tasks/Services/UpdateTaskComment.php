<?php

namespace Modules\Tasks\Services;

use Modules\Core\Models\Notification;
use Modules\Tasks\Models\TaskComment;

class UpdateTaskComment
{
    public static function call(TaskComment $taskComment, array $data): TaskComment
    {
        $taskComment->comment = preg_replace('/<p><br><\/p>(\s*<p><br><\/p>)*$/', '', $data['comment']);
        $taskComment->save();

        $taskCommentNotification = Notification::whereRaw("data like '%\"task_comment_id\":$taskComment->id,%'")->where('read_at', null)->first();
        if ($taskCommentNotification) {
            $data = json_decode($taskCommentNotification->data, true);
            $data['task_comment_id'] = $taskComment->id;
            $data['task_comment'] = $taskComment->comment;
            $taskCommentNotification->data = json_encode($data);
            $taskCommentNotification->save();
        } else {
            NotifyTaskComment::call($taskComment->task, $taskComment->comment, auth()->user(), $taskComment->id);
        }

        return $taskComment;
    }
}
