<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use App\Models\Permission;
use App\Models\Role;

trait HasRoles
{
    use HasPermissions;

    public function roles(): BelongsToMany
    {
        $relation = $this->belongsToMany(Role::class, 'user_has_roles', 'user_id', 'role_id');
        if (count(getPermissionsRestaurantIds()) > 0) {
            $relation->wherePivotIn('restaurant_id', getPermissionsRestaurantIds());
        }
        return $relation->where(function (Builder $q) {
                        $q->whereNull('roles.restaurant_id')->orWhereIn('roles.restaurant_id', getPermissionsRestaurantIds());
                    })
                    ->withTimestamps()
                    ->withPivot([
                        'role_uuid',
                        'user_uuid',
                        'restaurant_uuid'
                    ]);
    }

    /**
     * Scope the model query to certain roles only.
     *
     * @param  string|int|array|\App\Contracts\RoleContract|\Illuminate\Support\Collection  $roles
     */
    public function scopeRole(Builder $query, $roles): Builder
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        $roles = array_map(function ($role) {
            if ($role instanceof Role) {
                return $role;
            }

            $method = is_numeric($role) ? 'findById' : 'findByName';

            return Role::{$method}($role);
        }, Arr::wrap($roles));

        return $query->whereHas('roles', function (Builder $subQuery) use ($roles) {
            $key = (new Role())->getKeyName();
            $subQuery->whereIn("roles.{$key}", \array_column($roles, $key));
        });
    }

    /**
     * Assign the given role to the model.
     *
     * @param  array|string|int|\App\Contracts\RoleContract|\Illuminate\Support\Collection  ...$roles
     * @return $this
     */
    public function assignRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->reduce(function ($array, $role) {
                if (empty($role)) {
                    return $array;
                }

                $role = $this->getStoredRole($role);
                if (! $role instanceof Role) {
                    return $array;
                }

                $array[$role->getKey()] = ! is_a($this, Permission::class) ?
                    [
                        'user_uuid' => $this->uuid,
                        'role_uuid' => $role->uuid,
                        'restaurant_id' => getPermissionsRestaurantId(),
                        'restaurant_uuid' => getPermissionsRestaurantUuid(),
                    ] : [];

                return $array;
            }, []);

        $model = $this->getModel();

        if ($model->exists) {
            $this->roles()->sync($roles, false);
            $model->load('roles');
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($roles, $model) {
                    if ($model->getKey() != $object->getKey()) {
                        return;
                    }
                    $model->roles()->sync($roles, false);
                    $model->load('roles');
                }
            );
        }

        if (is_a($this, Permission::class)) {
            $this->forgetCachedPermissions();
        }

        return $this;
    }

    /**
     * Revoke the given role from the model.
     *
     * @param  string|int|\Spatie\Permission\Contracts\Role  $role
     */
    public function removeRole($role)
    {
        $this->roles()->syncWithPivotValues(
            $this->getStoredRole($role),
            ['deleted_at' => Carbon::now()]
        );

        $this->load('roles');

        if (is_a($this, Permission::class)) {
            $this->forgetCachedPermissions();
        }

        return $this;
    }

    protected function getStoredRole($role): Role
    {
        if (is_numeric($role)) {
            return Role::findById($role);
        }

        if (is_string($role)) {
            return Role::findByName($role);
        }

        return $role;
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param  string|int|array|\App\Contracts\RoleContract|\Illuminate\Support\Collection  $roles
     */
    public function hasRole($roles): bool
    {
        $this->loadMissing('roles');

        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if (is_int($roles)) {
            $key = (new Role())->getKeyName();

            return $this->roles->contains($key, $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains($roles->getKeyName(), $roles->getKey());
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }

            return false;
        }

        return $roles->intersect($this->roles)->isNotEmpty();
    }

    /**
     * Determine if the model has any of the given role(s).
     *
     * Alias to hasRole() but without Guard controls
     *
     * @param  string|int|array|\App\Contracts\RoleContract|\Illuminate\Support\Collection  $roles
     */
    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param  string|array|\App\Contracts\RoleContract|\Illuminate\Support\Collection  $roles
     */
    public function hasAllRoles($roles): bool
    {
        $this->loadMissing('roles');

        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains($roles->getKeyName(), $roles->getKey());
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect($this->getRoleNames()) == $roles;
    }

    /**
     * Determine if the model has exactly all of the given role(s).
     *
     * @param  string|array|\App\Contracts\RoleContract|\Illuminate\Support\Collection  $roles
     */
    public function hasExactRoles($roles): bool
    {
        $this->loadMissing('roles');

        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            $roles = [$roles];
        }

        if ($roles instanceof Role) {
            $roles = [$roles->name];
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $this->roles->count() == $roles->count() && $this->hasAllRoles($roles);
    }

    protected function convertPipeToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (! in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }
}
