<?php

namespace Modules\Core\Services;

use Modules\Core\Models\Notification;
use Modules\Users\Models\User;

class ListNotification
{
    protected static $per_page = 4;

    protected static $types = [
        'task' => 'Modules\Tasks\Notifications\TaskNotification',
    ];

    public static function call($user, $type)
    {
        $user_id = auth()->id();
        $notifications = Notification::where('notifiable_id', $user_id)
            ->where('notifiable_type', 'Modules\Users\Models\User')
            ->where('type', self::$types[$type] ?? '')
            ->paginate(self::$per_page);

        return [
            'messages' => self::formatData($notifications),
            'countMessagesUnRead' => Notification::where('notifiable_id', $user_id)->where('read_at', null)->count(),
        ];
    }

    protected static function formatData($notifications)
    {
        $notifications->transform(function ($notification) {
            $notification_data = $notification['data'];

            $notifier_user = User::find($notification_data['notifier_user_id']);
            $avatar = $notifier_user->avatar_url;

            return [
                'id' => $notification['id'],
                'task_id' => $notification_data['task_id'],
                'notifier_user_avatar' => $avatar,
                'description' => str_replace('&nbsp;', ' ', strip_tags($notification_data['task_comment'])),
                'created_at' => $notification['created_at'],
                'read_at' => $notification['read_at'],
            ];
        });

        return $notifications;
    }
}
