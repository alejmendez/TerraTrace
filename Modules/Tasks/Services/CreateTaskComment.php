<?php

namespace Modules\Tasks\Services;

use Modules\Tasks\Models\TaskComment;
use Modules\Users\Models\User;

class CreateTaskComment
{
    public static function call(array $data, User $user): TaskComment
    {
        $taskComment = new TaskComment;
        $taskComment->task_id = $data['task_id'];
        $taskComment->comment = preg_replace('/<p><br><\/p>(\s*<p><br><\/p>)*$/', '', $data['comment']);
        $taskComment->user_id = $user->id;
        $taskComment->save();

        NotifyTaskComment::call($taskComment->task, $taskComment->comment, $user, $taskComment->id);

        return $taskComment;
    }
}
