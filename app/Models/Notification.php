<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory, HasUuid;

    protected $guarded = [];

    public function notifiable(): MorphTo
    {
        return $this->morphTo('notifiable');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_notification', 'notification_id', 'user_id')
            ->using(UserNotification::class)
            ->withTimestamps()
            ->withPivot([
                'user_uuid',
                'notification_uuid',
                'read_at',
            ]);
    }
}
