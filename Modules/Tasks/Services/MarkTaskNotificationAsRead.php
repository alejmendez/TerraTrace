<?php

namespace Modules\Tasks\Services;

class MarkTaskNotificationAsRead
{
    protected static $typeNotification = 'Modules\Tasks\Notifications\TaskNotification';

    public static function call($task, $user)
    {
        $notifications = $user->notifications
            ->where('type', self::$typeNotification)
            ->whereNull('read_at')
            ->filter(function ($notification) use ($task) {
                return $notification->data['task_id'] === $task->id;
            });

        if ($notifications->isEmpty()) {
            return;
        }

        $notifications->markAsRead();
    }
}
