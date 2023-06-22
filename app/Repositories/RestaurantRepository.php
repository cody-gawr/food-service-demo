<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\{Restaurant, User};
use Illuminate\Support\Carbon;

class RestaurantRepository
{
    /**
     * @param \App\Models\Restaurant
     */
    public function __construct(
        public readonly Restaurant $restaurant
    ) {}

    /**
     * @param string $uuid
     *
     * @return \App\Models\Restaurant|null
     */
    public function findByUuid(string $uuid): Restaurant|null {
        return $this->restaurant->where('uuid', $uuid)->first();
    }

    /**
     * @param \Illuminate\Support\Collection $keywords
     * @param \Illuminate\Support\Collection $orderBys
     * @param \App\Models\User|null  $user
     * @param int $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getRestaurants(Collection $keywords, Collection $orderBys, User $user = null, int $perPage = 10): LengthAwarePaginator
    {
        /**
         * @var \Illuminate\Database\Eloquent\Builder
         */
        $queryBuilder = $this->restaurant->query();
        if (! is_null($user)) {
            $queryBuilder->withCount('users')->with(['users' => function (BelongsToMany $builder) use ($user) {
                $builder->where('user_id', $user->id);
            }]);
        }

        $keywords->each(function ($value, $column) use ($queryBuilder) {
            if ($column == 'rating') {
                $queryBuilder->where($column, $value);
            } else {
                $queryBuilder->where($column, 'like', "%{$value}%");
            }
        });

        $orderBys->each(function ($value, $column) use ($queryBuilder) {
            $queryBuilder->orderBy($column, $value);
        });

        return $queryBuilder->paginate($perPage);
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     *
     * @return bool
     */
    public function isSubscribing(User $user, Restaurant $restaurant): bool
    {
        return !is_null(
            $user->restaurants()
            ->wherePivot('restaurant_id', $restaurant->id)
            ->wherePivotNull('deleted_at')
            ->first()
        );
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     *
     * @return bool
     */
    public function isOwner(User $user, Restaurant $restaurant): bool
    {
        return !is_null(
            $user->restaurants()
            ->wherePivot('restaurant_id', $restaurant->id)
            ->wherePivotNotNull('approved_at')
            ->wherePivotNull('deleted_at')
            ->first()
        );
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     *
     * @return array
     */
    public function subscribe(User $user, Restaurant $restaurant): array
    {
        return $user->restaurants()
            ->syncWithPivotValues($restaurant, [
                'user_uuid' => $user->uuid,
                'restaurant_uuid' => $restaurant->uuid,
                'deleted_at' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ], false);
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     *
     * @return array
     */
    public function unsubscribe(User $user, Restaurant $restaurant): array
    {
        return $user->restaurants()
            ->syncWithPivotValues($restaurant, [
                // 'updated_at' => Carbon::now(),
                'deleted_at' => Carbon::now(),
                'updated_by' => $user->id,
            ], false);
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @param array  $documents
     *
     * @return array
     */
    public function claimOwner(User $user, Restaurant $restaurant, array $documents = []): array
    {
        return $user->restaurants()
            ->syncWithPivotValues($restaurant, [
                'user_uuid' => $user->uuid,
                'restaurant_uuid' => $restaurant->uuid,
                'documents' => $documents,
                'is_claming_owner' => true,
                'deleted_at' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ], false);
    }

     /**
     * @param \App\Models\User  $admin
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     *
     * @return void
     */
    public function approve(User $admin, User $user, Restaurant $restaurant): void
    {
        $restaurant->users()->syncWithPivotValues(
            $user,
            [
                'approved_at' => Carbon::now(),
                'approved_by' => $admin->id,
            ],
            false
        );
    }
}
