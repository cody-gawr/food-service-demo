<?php

namespace App\Providers;

use App\Contracts\RestaurantContract;
use App\Contracts\UserContract;
use App\Models\Post;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\Response;
use App\Models\Restaurant;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(UserContract $userContract, RestaurantContract $restaurantContract): void
    {
        Gate::define('unlock-restaurant-profile-with-only-text', function (User $user, Restaurant $restaurant) use ($userContract) {
            return $userContract->hasPermissionInRestaurant($user, 'unlock restaurant profile with only text', $restaurant) ?
                Response::allow() :
                Response::deny('You do not have the permission to unlock the profile.', 403);
        });

        Gate::define('unlock-restaurant-profile-with-image', function (User $user, Restaurant $restaurant) use ($userContract) {
            return $userContract->hasPermissionInRestaurant($user, 'unlock restaurant profile with image', $restaurant) ?
                Response::allow() :
                Response::deny('You do not have the permission to unlock the profile with images.', 403);
        });

        Gate::define('unlock-restaurant-profile-with-video', function (User $user, Restaurant $restaurant) use ($userContract) {
            return $userContract->hasPermissionInRestaurant($user, 'unlock restaurant profile with video', $restaurant) ?
                Response::allow() :
                Response::deny('You do not have the permission to unlock the profile with videos.', 403);
        });

        Gate::define('restaurant-has-post', function (User $user, Restaurant $restaurant, Post $post) use ($restaurantContract) {
            return $restaurantContract->hasPost($restaurant, $post) ?
                Response::allow() :
                Response::deny('Restaurant does not have the post.', 403);
        });

        Gate::define('restaurant-has-user', function (User $user, Restaurant $restaurant) {
            return $restaurant->users()->where('id', $user->id)->exists() ?
                Response::allow() :
                Response::deny('Restaurant does not have the user.', 403);
        });

        Gate::define('unlock-sponsored-posts-and-ads', function (User $user, Restaurant $restaurant) use ($userContract) {
            return $userContract->hasPermissionInRestaurant($user, 'unlock sponsored posts and ads', $restaurant) ?
                Response::allow() :
                Response::deny('You do not have the permission to unlock the profile with images.', 403);
        });

        Gate::define('admin', function (User $user) {
            return $user->hasRole('admin') ?
                Response::allow() :
                Response::deny('User is not an admin.', 403);
        });
    }
}
