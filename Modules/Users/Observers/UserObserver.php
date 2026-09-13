<?php

namespace Modules\Users\Observers;

use Illuminate\Foundation\Auth\User;

class UserObserver
{
    private int $savedTtl = 600;

    private int $restoredTtl = 600;

    private int $retrievedTtl = 600;

    public function saved(User $user): void
    {
        cache()->put("auth.user.{$user->id}", $user, $this->savedTtl);
    }

    public function deleted(User $user): void
    {
        cache()->forget("auth.user.{$user->id}");
    }

    public function restored(User $user): void
    {
        cache()->put("auth.user.{$user->id}", $user, $this->restoredTtl);
    }

    public function retrieved(User $user): void
    {
        cache()->add("auth.user.{$user->id}", $user, $this->retrievedTtl);
    }
}
