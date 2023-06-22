<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class RestaurantProfile extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $table = 'restaurant_profiles';

    protected $guarded = [];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
