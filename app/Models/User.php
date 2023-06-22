<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasRestaurants;
use App\Traits\HasRoles;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\MorphOne;

// use Spatie\Permission\Traits\HasRoles as SpatieHasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid, MustVerifyEmail, SoftDeletes, HasRoles, HasRestaurants;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'name',
        'email',
        'password',
        'avatar',
        'address',
        'interesting',
        'intrigue',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
        'pivot.leader_id',
        'pivot.follower_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'interesting' => 'array',
        'intrigue' => 'array'
    ];

    protected $appends = ['avatar_url'];

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = $this->attributesToArray();
        $attributes = array_merge($attributes, $this->relationsToArray());
        unset($attributes['pivot']['leader_id'], $attributes['pivot']['follower_id']);
        return $attributes;
    }

    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn(string|null $value) => is_null($value) ? $value : ucfirst($value),
            set: fn(string $value) => strtolower($value)
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn(string|null $value) => is_null($value) ? $value : ucfirst($value),
            set: fn(string $value) => strtolower($value)
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => strtolower($value)
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => strtolower($value)
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => Hash::make($value)
        );
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => Storage::disk('public')->url($this->avatar)
        );
    }

    public function personalVerificationCodes(): HasMany
    {
        return $this->hasMany(PersonalVerificationCode::class, 'user_id');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follower_following', 'leader_id', 'follower_id')
                    ->withTimestamps()
                    ->withPivot(['leader_uuid', 'follower_uuid']);
    }

    public function followings(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follower_following', 'follower_id', 'leader_id')
                    ->withTimestamps()
                    ->withPivot(['leader_uuid', 'follower_uuid']);
    }

    /**
     * @param Builder $query
     * @param int $id
     *
     * @return Builder
     */
    public function scopeWithIsFollowerOfUser(Builder $query, int $id): Builder
    {
        return $query->addSelect([
            'is_follower' =>
                FollowerFollowing::selectRaw('COUNT(*)')
                    ->whereColumn('follower_id', 'users.id')
                    ->where('leader_id', $id)
                    ->take(1)
        ])
        ->withCasts(['is_follower' => 'boolean']);
    }

    /**
     * @param Builder $query
     * @param int $id
     *
     * @return Builder
     */
    public function scopeWithIsUserFollowing(Builder $query, int $id): Builder
    {
        return $query->addSelect(
                [
                    'is_following' =>
                        FollowerFollowing::selectRaw('COUNT(*)')
                            ->whereColumn('leader_id', 'users.id')
                            ->where('follower_id', $id)
                            ->take(1)
                ]
            )
            ->withCasts(['is_following' => 'boolean']);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereHas('roles', function (Builder $query) {
            $query->where('name', 'admin');
        });
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'user_notification', 'user_id', 'notification_id')
            ->using(UserNotification::class)
            ->withTimestamps()
            ->withPivot([
                'user_uuid',
                'notification_uuid',
                'read_at',
            ]);
    }

    public function notification(): MorphOne
    {
        return $this->morphOne(Notification::class, 'notifiable');
    }
}
