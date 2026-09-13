<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Services\ListNotification;

class NotificationsController extends Controller
{
    public function unread()
    {
        $user = auth()->user();
        $type = request()->route('type');

        return ListNotification::call($user, $type);
    }
}
