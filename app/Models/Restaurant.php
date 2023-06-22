<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Chelout\RelationshipEvents\Concerns\HasManyEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\NotificationContract;

class Restaurant extends Model
{
    use HasFactory, HasManyEvents;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'restaurants';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'scraping_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'images' => 'array',
        'types' => 'array',
        'open_closed_time' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::hasManyCreated(function (Restaurant $parent, Model $related) {
            if ($related instanceof Post || $related instanceof Ad) {
                /** @var \App\Contracts\NotificationContract */
                $notificationContract = app(NotificationContract::class);
                $notificationContract->create($related);
            }
        });
    }

    /**
     * @param mixed  $value
     * @param  string|null  $field
     * @return \App\Models\Restaurant
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('uuid', $value)->firstOrFail();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_user', 'restaurant_id', 'user_id')
            ->using(RestaurantUser::class)
            ->withTimestamps()
            ->withPivot([
                'restaurant_uuid',
                'user_uuid',
                'documents',
                'is_claming_owner',
                'approved_at'
            ]);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(RestaurantProfile::class, 'restaurant_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'restaurant_id');
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class, 'restaurant_id');
    }
}
