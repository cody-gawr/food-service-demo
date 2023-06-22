<?php

namespace App\Models;

use Chelout\RelationshipEvents\Concerns\HasMorphOneEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\NotificationContract;
use App\Traits\HasUuid;

class Ad extends Model
{
    use HasFactory, HasUuid, SoftDeletes, HasMorphOneEvents;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ads';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::morphOneCreated(function (Ad $parent, Model $related) {
            if ($related instanceof Notification) {
                /** @var \App\Contracts\NotificationContract */
                $notificationContract = app(NotificationContract::class);
                $notificationContract->attachUsers($related, $parent);
            }
        });
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(
            Image::class,
            'imageable',
            'imageable_type',
        );
    }

    public function videos(): MorphMany
    {
        return $this->morphMany(
            Video::class,
            'videoable',
            'videoable_type',
        );
    }

    public function notification(): MorphOne
    {
        return $this->morphOne(Notification::class, 'notifiable');
    }
}
