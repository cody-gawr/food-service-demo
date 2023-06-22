<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Contracts\NotificationContract;
use App\Models\Ad;
use App\Models\Notification;
use App\Models\Post;

class NotificationService implements NotificationContract
{

    public function __construct(
        public readonly NotificationRepository $notificationRepository
    ) {}

    public function create(Post|Ad $related): Notification
    {
        return $this->notificationRepository->create($related);
    }

    /**
     * @param \App\Models\Notification  $notification
     * @param \App\Models\Post|\App\Models\Ad  $related
     *
     * @return void
     */
    public function attachUsers(Notification $notification, Post|Ad $related): void
    {
        $this->notificationRepository->attachUsers($notification, $related);
    }
}
