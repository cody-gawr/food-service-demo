<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Models\Post;
use App\Models\Ad;
use App\Models\Restaurant;
use App\Models\User;

class NotificationRepository
{
    public function __construct(
        public readonly Notification $notification
    ) {}

    public function create(Post|Ad $related)
    {
        return $related->notification()->create([
            'notifiable_uuid' => $related->uuid
        ]);
    }

    public function attachUsers(Notification $notification, Post|Ad $related): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection */
        $admins = User::admins()->get();
        /** @var \App\Models\User */
        $owner = $related->createdBy->load('restaurants.users');
        /** @var array */
        $pivotDataByUserId = $owner->restaurants->flatMap(function (Restaurant $restaurant) use ($admins) {
            return $restaurant->users->merge($admins)->unique('id');
        })->reduce(function (array $values, User $user) use ($notification) {
            $values[$user->id] = [
                'user_uuid' => $user->uuid,
                'notification_uuid' => $notification->uuid,
            ];
            return $values;
        }, []);

        $notification->users()->attach($pivotDataByUserId);
    }
}
