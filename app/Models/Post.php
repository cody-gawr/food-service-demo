<?php

namespace App\Models;

use Chelout\RelationshipEvents\Concerns\HasMorphOneEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\NotificationContract;
use App\Traits\HasUuid;

class Post extends Model
{
    use HasFactory, HasUuid, SoftDeletes, HasMorphOneEvents;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'posts';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::morphOneCreated(function (Post $parent, Model $related) {
            if ($related instanceof Notification) {
                /** @var \App\Contracts\NotificationContract */
                $notificationContract = app(NotificationContract::class);
                $notificationContract->attachUsers($related, $parent);
            }
        });
    }

    /**
     * @param mixed  $value
     * @param  string|null  $field
     * @return \App\Models\Post
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('uuid', $value)->firstOrFail();
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function videos(): MorphMany
    {
        return $this->morphMany(Video::class, 'videoable');
    }

    public function notification(): MorphOne
    {
        return $this->morphOne(Notification::class, 'notifiable');
    }
}
