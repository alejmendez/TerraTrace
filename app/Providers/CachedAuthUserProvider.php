<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Modules\Users\Models\User;

class CachedAuthUserProvider extends EloquentUserProvider
{
    public function __construct(HasherContract $hasher)
    {
        parent::__construct($hasher, User::class);
    }

    /**
     * @param  mixed  $identifier
     * @return Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return cache()->remember(
            'user_'.$identifier,
            now()->addHours(2),
            fn () => parent::retrieveById($identifier)
        );
    }
}
