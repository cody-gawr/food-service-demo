<?php

use App\Models\Restaurant;

if (! function_exists('setPermissionsRestaurantIdAndUuid')) {
    /**
     * @param \App\Models\Restaurant  $restaurant
     */
    function setPermissionsRestaurantIdAndUuid(Restaurant $restaurant)
    {
        app(\App\Managers\PermissionRegistrar::class)->setPermissionsRestaurantIdAndUuid($restaurant);
    }
}

if (! function_exists('setPermissionsRestaurantId')) {
    /**
     * @param int|\App\Models\Restaurant  $restaurantId
     */
    function setPermissionsRestaurantId($restaurantId)
    {
        app(\App\Managers\PermissionRegistrar::class)->setPermissionsRestaurantId($restaurantId);
    }
}

if (! function_exists('setPermissionsRestaurantUuid')) {
    /**
     * @param string|\App\Models\Restaurant  $restaurantUuid
     */
    function setPermissionsRestaurantUuid($restaurantUuid)
    {
        app(\App\Managers\PermissionRegistrar::class)->setPermissionsRestaurantUuid($restaurantUuid);
    }
}

if (! function_exists('setPermissionsRestaurantIds')) {
    /**
     * @param array<int>|\Illuminate\Database\Eloquent\Collection  $restaurantIds
     */
    function setPermissionsRestaurantIds(array $restaurantIds)
    {
        app(\App\Managers\PermissionRegistrar::class)->setPermissionsRestaurantIds($restaurantIds);
    }
}

if (! function_exists('getPermissionsRestaurantId')) {
    /**
     * @return int
     */
    function getPermissionsRestaurantId()
    {
        return app(\App\Managers\PermissionRegistrar::class)->getPermissionsRestaurantId();
    }
}

if (! function_exists('getPermissionsRestaurantUuid')) {
    /**
     * @return int
     */
    function getPermissionsRestaurantUuid()
    {
        return app(\App\Managers\PermissionRegistrar::class)->getPermissionsRestaurantUuid();
    }
}

if (! function_exists('getPermissionsRestaurantIds')) {
    /**
     * @return array<int>
     */
    function getPermissionsRestaurantIds()
    {
        return app(\App\Managers\PermissionRegistrar::class)->getPermissionsRestaurantIds();
    }
}
