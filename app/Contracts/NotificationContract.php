<?php

namespace App\Contracts;

use App\Models\Ad;
use App\Models\Notification;
use App\Models\Post;

interface NotificationContract
{
    /**
     * @param \App\Models\Post|\App\Models\Ad  $related
     *
     * @return \App\Models\Notification
     */
    public function create(Post|Ad $related): Notification;

    /**
     * @param \App\Models\Notification  $notification
     * @param \App\Models\Post|\App\Models\Ad  $related
     *
     * @return void
     */
    public function attachUsers(Notification $notification, Post|Ad $related): void;
}
