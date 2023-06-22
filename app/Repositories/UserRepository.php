<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use App\Models\User;

class UserRepository
{
    /**
     * @param \App\Models\User $user
     */
    public function __construct(
       public readonly User $user
    ) {}

    /**
     * @param array $attributes
     *
     * @return User
     */
    public function create(array $attributes): User
    {
        $userInstance = $this->user->newInstance();

        $userInstance->fill($attributes);
        $userInstance->save();

        return $userInstance;
    }

    /**
     * @param string $uuid
     *
     * @return \App\Models\User|null
     */
    public function findByUuid(string $uuid): ?User
    {
        return $this->user->where('uuid', $uuid)->first();
    }

    /**
     * @param string $email
     *
     * @return \App\Models\User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->user->where('email', $email)->first();
    }

    /**
     * @param \App\Models\User  $user
     * @param array  $attributes
     *
     * @return bool
     */
    public function update(User $user, array $attributes): bool
    {
        return $user->update($attributes);
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\User  $leader
     *
     * @return bool
     */
    public function follow(User $user, User $leader): void
    {
        $user->followings()->syncWithPivotValues(
            $leader,
            [
                'leader_uuid' => $leader->uuid,
                'follower_uuid' => $user->uuid,
                'deleted_at' => null
            ]
        );
    }

    public function unfollow(User $user, User $leader): void
    {
        $user->followings()->syncWithPivotValues($leader, ['deleted_at' => Carbon::now()]);
    }

    public function isFollowing(User $user, User $leader): bool
    {
        return ! is_null(
            $user->followings()
                ->wherePivotNull('deleted_at')
                ->where('leader_id', $leader->id)
                ->first()
            );
    }

    public function isLeading(User $user, User $follower): bool
    {
        return ! is_null(
            $user->followers()
                ->wherePivotNull('deleted_at')
                ->where('id', $follower->id)
                ->first()
            );
    }

    /**
     * @param int $userId
     * @param \Illuminate\Support\Collection $keywords
     * @param int $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUsers(int $userId, Collection $keywords, int $perPage = 10): LengthAwarePaginator
    {
        /**
         * @var \Illuminate\Database\Eloquent\Builder
         */
        $queryBuilder = $this->user->withIsFollowerOfUser($userId)
                            ->withIsUserFollowing($userId)
                            ->withCount(['followers', 'followings'])
                            ->withCasts(['is_follower' => 'boolean', 'is_following' => 'boolean'])
                            ->whereNot('id', $userId);

        $keywords->each(function ($value, $column) use ($queryBuilder) {
            $queryBuilder->where($column, 'like', "%{$value}%");
        });

        return $queryBuilder->paginate($perPage);
    }

    public function getFollowers(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->followers()->withCount(['followers', 'followings'])->paginate($perPage);
    }

    public function getFollowings(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->followings()->withCount(['followers', 'followings'])->paginate($perPage);
    }
}
