<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\Auth\SendVerficationCode;
use App\Listeners\Auth\SendSecretCode;
use App\Events\Auth\PasswordForgot;
use App\Events\Auth\UserRegistered;
use App\Events\User\EmailUpdated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        EmailUpdated::class => [
            SendVerficationCode::class
        ],
        UserRegistered::class => [
            SendVerficationCode::class
        ],
        PasswordForgot::class => [
            SendSecretCode::class
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
