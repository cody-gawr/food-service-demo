<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Exceptions\Permission\RoleDoesNotExist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\RoleContract;
use App\Traits\HasPermissions;
use App\Traits\HasUuid;

class Role extends Model implements RoleContract
{
    use HasFactory, HasUuid, HasPermissions;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'restaurant_id',
        'deleted_at'
    ];

    protected $guarded = [];

    /**
     * A role may be given various permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id')
                    ->withPivot([
                        'role_uuid',
                        'permission_uuid'
                    ])
                    ->withTimestamps();
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_has_roles', 'role_id', 'user_id')
                    ->withPivot([
                        'role_uuid',
                        'user_uuid',
                        'restaurant_uuid'
                    ])
                    ->withTimestamps();
    }

    protected static function findByParam(array $params = [])
    {
        $query = static::query();

        $query->where(function (Builder $q) use ($params) {
            $q->whereNull('restaurant_id')->orWhere('restaurant_id', $params['restaurant_id'] ?? getPermissionsRestaurantId());
        });
        unset($params['restaurant_id']);

        foreach ($params as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }

    public static function findByName(string $name): RoleContract
    {
        $role = static::findByParam(['name' => $name]);

        if (! $role) {
            throw RoleDoesNotExist::named($name);
        }

        return $role;
    }

    public static function findById(int $id): RoleContract
    {
        $role = static::findByParam([(new static())->getKeyName() => $id]);

        if (! $role) {
            throw RoleDoesNotExist::withId($id);
        }

        return $role;
    }

    public static function findOrCreate(string $name): RoleContract
    {
        $role = static::findByParam(['name' => $name]);

        if (! $role) {
            return static::query()->create(['name' => $name] + ['restaurant_id' => getPermissionsRestaurantId(), 'restaurant_uuid' => getPermissionsRestaurantUuid()]);
        }

        return $role;
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param  string|Permission  $permission
     */
    public function hasPermissionTo($permission): bool
    {
        return false;
    }
}
