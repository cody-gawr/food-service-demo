<?php

namespace App\Traits;

use App\Exceptions\Permission\PermissionDoesNotExist;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use App\Managers\PermissionRegistrar;
use Illuminate\Support\Arr;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Carbon;

trait HasPermissions
{
    /**
     * A model may have multiple direct permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_has_permissions', 'user_id', 'permission_id')
            ->wherePivotIn('restaurant_id', getPermissionsRestaurantIds())
            ->withTimestamps()
            ->withPivot([
                'user_uuid',
                'permission_uuid',
                'restaurant_uuid'
            ]);
    }

    /**
     * Scope the model query to certain permissions only.
     *
     * @param  string|int|array|\App\Contracts\PermissionContract|\Illuminate\Support\Collection  $permissions
     */
    public function scopePermission(Builder $query, $permissions): Builder
    {
        $permissions = $this->convertToPermissionModels($permissions);

        $rolesWithPermissions = array_unique(array_reduce($permissions, function ($result, $permission) {
            return array_merge($result, $permission->roles->all());
        }, []));

        return $query->where(function (Builder $query) use ($permissions, $rolesWithPermissions) {
            $query->whereHas('permissions', function (Builder $subQuery) use ($permissions) {
                $key = (new Permission)->getKeyName();
                $subQuery->whereIn("permissions.{$key}", \array_column($permissions, $key));
            });
            if (count($rolesWithPermissions) > 0) {
                $query->orWhereHas('roles', function (Builder $subQuery) use ($rolesWithPermissions) {
                    $key = (new Role())->getKeyName();
                    $subQuery->whereIn("roles.{$key}", \array_column($rolesWithPermissions, $key));
                });
            }
        });
    }

    /**
     * @param  string|int|array|\App\Contracts\PermissionContract|\Illuminate\Support\Collection  $permissions
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     */
    protected function convertToPermissionModels($permissions): array
    {
        if ($permissions instanceof BaseCollection) {
            $permissions = $permissions->all();
        }

        return array_map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission;
            }
            $method = is_string($permission) ? 'findByName' : 'findById';

            return Permission::{$method}($permission);
        }, Arr::wrap($permissions));
    }

    /**
     * Forget the cached permissions.
     */
    public function forgetCachedPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Return all permissions directly coupled to the model.
     */
    public function getDirectPermissions(): EloquentCollection
    {
        return $this->permissions;
    }

    public function getRoleNames(): BaseCollection
    {
        $this->loadMissing('roles');

        return $this->roles->pluck('name');
    }

    /**
     * @param  string|int|array|\App\Contracts\PermissionContract|\Illuminate\Support\Collection  $permissions
     * @return \App\Contracts\PermissionContract|\App\Contracts\PermissionContract[]|\Illuminate\Support\Collection
     */
    protected function getStoredPermission($permissions)
    {

        if (is_numeric($permissions)) {
            return Permission::findById($permissions);
        }

        if (is_string($permissions)) {
            return Permission::findByName($permissions);
        }

        if (is_array($permissions)) {
            $permissions = array_map(function ($permission) {
                return is_a($permission, Permission::class) ? $permission->name : $permission;
            }, $permissions);

            return Permission::whereIn('name', $permissions)->get();
        }

        return $permissions;
    }

    /**
     * Returns permissions ids as array keys
     *
     * @param  string|int|array|\App\Contracts\PermissionContract|\Illuminate\Support\Collection  $permissions
     * @return array
     */
    public function collectPermissions(...$permissions)
    {
        return collect($permissions)
            ->flatten()
            ->reduce(function ($array, $permission) {
                if (empty($permission)) {
                    return $array;
                }

                $permission = $this->getStoredPermission($permission);
                if (! $permission instanceof Permission) {
                    return $array;
                }

                $array[$permission->getKey()] = is_a($this, Role::class) ?
                    [
                        'role_uuid' => $this->uuid,
                        'permission_uuid' => $permission->uuid
                    ] : [];

                return $array;
            }, []);
    }

    /**
     * Grant the given permission(s) to a role.
     *
     * @param  string|int|array|\App\Contracts\PermissionContract|\Illuminate\Support\Collection  $permissions
     * @return $this
     */
    public function givePermissionTo(...$permissions)
    {
        $permissions = $this->collectPermissions(...$permissions);

        $model = $this->getModel();

        if ($model->exists) {
            $this->permissions()->sync($permissions, false);
            $model->load('permissions');
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($permissions, $model) {
                    if ($model->getKey() != $object->getKey()) {
                        return;
                    }
                    $model->permissions()->sync($permissions, false);
                    $model->load('permissions');
                }
            );
        }

        if (is_a($this, Role::class)) {
            $this->forgetCachedPermissions();
        }

        return $this;
    }

    /**
     * Revoke the given permission(s).
     *
     * @param  \App\Contracts\PermissionContract|\App\Contracts\PermissionContract[]|string|string[]  $permission
     * @return $this
     */
    public function revokePermissionTo($permission)
    {
        $this->permissions()->syncWithPivotValues($this->getStoredPermission($permission), [
            'deleted_at' => Carbon::now()
        ]);

        if (is_a($this, Role::class)) {
            $this->forgetCachedPermissions();
        }

        $this->load('permissions');

        return $this;
    }

    public function getPermissionNames(): BaseCollection
    {
        return $this->permissions->pluck('name');
    }

    /**
     * Find a permission.
     *
     * @param  string|int|\App\Contracts\PermissionContract  $permission
     * @return \App\Contracts\PermissionContract
     *
     * @throws \App\Exceptions\Permission\PermissionDoesNotExist
     */
    public function filterPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission);
        }

        if (is_int($permission)) {
            $permission = Permission::findById($permission);
        }

        if (! $permission instanceof Permission) {
            throw new PermissionDoesNotExist();
        }

        return $permission;
    }

    /**
     * Determine if the model may perform the given permission.
     * @param string|int|\App\Contracts\PermissionContract  $permission
     *
     * @throws \App\Exceptions\Permission\PermissionDoesNotExist
     */
    public function hasPermissionTo($permission): bool
    {
        $permission = $this->filterPermission($permission);

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param  string|int|\App\Contracts\PermissionContract  $permission
     *
     * @throws \App\Exceptions\Permission\PermissionDoesNotExist
     */
    public function hasDirectPermission($permission): bool
    {
        /** @var \App\Models\Permission */
        $permission = $this->filterPermission($permission);

        return $this->permissions->contains($permission->getKeyName(), $permission->getKey());
    }

    /**
     * Determine if the model has, via roles, the given permission.
     */
    protected function hasPermissionViaRole(Permission $permission): bool
    {
        return $this->hasRole($permission->roles);
    }

    /**
     * Return all the permissions the model has via roles.
     */
    public function getPermissionsViaRoles(): BaseCollection
    {
        return $this->loadMissing('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions;
            })->sort()->values();
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     */
    public function getAllPermissions(): BaseCollection
    {
        /** @var \Illuminate\Database\Eloquent\Collection $permissions */
        $permissions = $this->permissions;

        if (method_exists($this, 'roles')) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->sort()->values();
    }
}
