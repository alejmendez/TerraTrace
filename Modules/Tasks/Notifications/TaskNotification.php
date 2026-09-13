<?php

namespace Modules\Tasks\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskNotification extends Notification
{
    use Queueable;

    protected $task_data;

    public function __construct($task_data)
    {
        $this->task_data = $task_data;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $user): array
    {
        return $this->task_data;
    }
}
