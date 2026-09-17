<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Modules\Users\Models\User;

/**
 * CachedAuthUserProvider
 *
 * Extends EloquentUserProvider to add a thin cache layer around
 * `retrieveById()`. The TTL matches `CacheService::getUserDataSession`
 * (10 minutes) so user-data and auth snapshots share the same
 * lifetime — a single `CacheService::clearUserCache($user)` drops
 * everything we know about a user, instead of leaving a stale
 * 2-hour copy of the Eloquent model in the auth path.
 *
 * Trade-offs:
 *   - Octane: in long-lived workers this cache persists across
 *     requests and reduces DB hits on every authenticated call.
 *   - Stale writes: if a user/role/permission changes without the
 *     caller invalidating `CacheService::clearUserCache`, requests
 *     keep seeing the stale snapshot until the TTL expires. Callers
 *     that mutate users MUST call `CacheService::clearUserCache($user)`.
 */
class CachedAuthUserProvider extends EloquentUserProvider
{
    /**
     * Cache TTL for the auth user lookup, in seconds.
     *
     * Picked to match Modules\Core\Services\CacheService::getUserDataSession
     * so both layers expire together. 10 minutes balances:
     *   - low DB load (auth hits the User row at most every 10 min
     *     per user per worker),
     *   - acceptable staleness window after a missed invalidation.
     */
    private const CACHE_TTL_SECONDS = 600;

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
            self::CACHE_TTL_SECONDS,
            fn () => parent::retrieveById($identifier)
        );
    }
}
