<?php

namespace App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use App\Managers\PermissionRegistrar;
use App\Contracts\AdContract;
use App\Contracts\RestaurantContract;
use App\Contracts\PermissionContract;
use App\Contracts\AuthContract;
use App\Contracts\NotificationContract;
use App\Contracts\PostContract;
use App\Contracts\UserContract;
use App\Contracts\RoleContract;
use App\Services\RestaurantService;
use App\Services\AuthService;
use App\Services\UserService;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AdService;
use App\Services\NotificationService;
use App\Services\PostService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthContract::class, AuthService::class);
        $this->app->bind(UserContract::class, UserService::class);
        $this->app->bind(RestaurantContract::class, RestaurantService::class);
        $this->app->bind(PermissionContract::class, Permission::class);
        $this->app->bind(RoleContract::class, Role::class);
        $this->app->bind(PostContract::class, PostService::class);
        $this->app->bind(AdContract::class, AdService::class);
        $this->app->bind(NotificationContract::class, NotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(PermissionRegistrar $permissionLoader): void
    {
        $this->app->singleton(PermissionRegistrar::class, function (Application $app) use ($permissionLoader) {
            return $permissionLoader;
        });
    }
}
