<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection as BaseCollection;
use App\Models\RestaurantUser;
use App\Models\Restaurant;

trait HasRestaurants
{
    public function restaurants(): BelongsToMany
    {
        return $this->belongsToMany(Restaurant::class, 'restaurant_user', 'user_id', 'restaurant_id')
            ->using(RestaurantUser::class)
            ->withTimestamps()
            ->withPivot([
                'user_uuid',
                'restaurant_uuid',
                'documents',
                'is_claming_owner',
                'approved_at'
            ]);

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ownedRestaurants(): BelongsToMany
    {
        return $this->restaurants()
                    ->wherePivotNotNull('approved_at')
                    ->withTimestamps()
                    ->withPivot([
                        'user_uuid',
                        'restaurant_uuid',
                        'documents',
                        'is_claming_owner',
                        'approved_at'
                    ]);
    }
    /**
     * @return array<int>
     */
    public function getRestaurantIdsOfOwner(): BaseCollection
    {
        $this->loadMissing('ownedRestaurants');

        return $this->ownedRestaurants->pluck('id');
    }
}
