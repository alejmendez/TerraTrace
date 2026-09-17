<?php

namespace Modules\Tasks\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskNotification extends Notification
{
    use Queueable;

    protected $task_data;

    public function __construct($task_data)
    {
        $this->task_data = $task_data;
    }

    /**
     * Channels this notification is delivered on.
     *
     * Currently:
     *   - `database`: persisted to the notifications table; surfaced
     *     via the menu's unread-counter (see CacheService::getUserUnreadNotifications).
     *
     * To enable real-time push in the SPA, add `broadcast` here and
     * configure a broadcast driver (Laravel Reverb, Pusher, etc.)
     * plus Echo on the frontend. The `broadcastOn()` and
     * `toBroadcast()` methods below are already wired so a config
     * change is the only thing missing.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->task_data;
    }

    /**
     * Channel + event name for the broadcast driver.
     *
     * Per-user private channel so each connected client only receives
     * its own task notifications.
     */
    public function broadcastOn(): array
    {
        return ['private-App.Models.User.'.($this->task_data['task_user_id'] ?? '')];
    }

    /**
     * Payload sent over the broadcast channel.
     *
     * Uses `BroadcastMessage` so the frontend receives just the
     * notification data (not the full DatabaseNotification model).
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->task_data);
    }
}
