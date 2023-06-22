<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Exceptions\Permission\PermissionDoesNotExist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\PermissionContract;
use App\Managers\PermissionRegistrar;
use App\Traits\HasUuid;

class Permission extends Model implements PermissionContract
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'deleted_at',
    ];

    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions', 'permission_id', 'role_id')
                    ->withPivot([
                        'permission_uuid',
                        'role_uuid',
                    ])
                    ->withTimestamps();
    }

    /**
     * A permission belongs to some users of the model associated with its guard.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_has_permissions', 'permission_id', 'user_id')
                    ->withPivot([
                        'permission_uuid',
                        'user_uuid',
                        'restaurant_uuid'
                    ])
                    ->withTimestamps();
    }

    /**
     * @param string $name
     * @throws \App\Exceptions\Permission\PermissionDoesNotExist
     */
    public static function findByName(string $name): PermissionContract
    {
        $permission = static::getPermission(['name' => $name]);

        if (! $permission) {
            throw PermissionDoesNotExist::create($name);
        }

        return $permission;
    }

    /**
     * @param int $id
     * @throws \App\Exceptions\Permission\PermissionDoesNotExist
     */
    public static function findById(int $id): PermissionContract
    {
        $permission = static::getPermission([(new static())->getKeyName() => $id]);

        if (! $permission) {
            throw PermissionDoesNotExist::withId($id);
        }

        return $permission;
    }

    /**
     * Find or create permission by its name.
     *
     * @param  string $name
     */
    public static function findOrCreate(string $name): PermissionContract
    {
        $permission = static::getPermission(['name' => $name]);

        if (! $permission) {
            return static::query()->create(['name' => $name]);
        }

        return $permission;
    }

    protected static function getPermissions(array $params = [], bool $onlyOne = false): Collection
    {
        return app(PermissionRegistrar::class)
                ->getPermissions($params, $onlyOne);
    }

    protected static function getPermission(array $params = []): ?PermissionContract
    {
        return static::getPermissions($params, true)->first();
    }
}
