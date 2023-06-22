<?php

namespace App\Managers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use App\Models\Permission;
use App\Models\Restaurant;
use App\Models\Role;
use InvalidArgumentException;

class PermissionRegistrar
{

    /** @var ?int */
    protected ?int $restaurantId = null;

    /** @var ?string */
    protected ?string $restaurantUuid = null;

    /** @var array<int> */
    protected array $restaurantIds = [];

    /** @var \Illuminate\Database\Eloquent\Collection */
    protected $permissions;

    /** @var \DateInterval|int */
    public static $cacheExpirationTime;

    /** @var string */
    public static string $cacheKey;

    /** @var array */
    private $except = ['created_at', 'updated_at', 'deleted_at'];

    /** @var array */
    private $cachedRoles = [];

    /** @var array */
    private $alias = [];

    public function __construct()
    {
        $this->initializeCache();
    }

    public function initializeCache()
    {
        self::$cacheExpirationTime = \DateInterval::createFromDateString('24 hours');
        self::$cacheKey = 'eatthat.permission.cache';
    }

    /**
     * Set the restaurant id for teams/groups support, this id is used when querying permissions/roles
     *
     * @param  \App\Models\Restaurant  $restaurant
     */
    public function setPermissionsRestaurantIdAndUuid($restaurant)
    {
        $this->restaurantId = $restaurant->getKey();
        $this->restaurantUuid = $restaurant->uuid;
    }

    /**
     * Set the restaurant id for teams/groups support, this id is used when querying permissions/roles
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model  $restaurantId
     */
    public function setPermissionsRestaurantId($restaurantId)
    {
        if ($restaurantId instanceof \App\Models\Restaurant) {
            $restaurantId = $restaurantId->getKey();
        }
        $this->restaurantId = $restaurantId;
    }

    /**
     * Set the restaurant id for teams/groups support, this id is used when querying permissions/roles
     *
     * @param  string|\Illuminate\Database\Eloquent\Model  $restaurantUuid
     */
    public function setPermissionsRestaurantUuid($restaurantUuid)
    {
        if ($restaurantUuid instanceof \App\Models\Restaurant) {
            $restaurantUuid = $restaurantUuid->uuid;
        }
        $this->restaurantUuid = $restaurantUuid;
    }

    /**
     * @param array<int>|\Illuminate\Database\Eloquent\Collection $restaurantIds
     */
    public function setPermissionsRestaurantIds($restaurantIds)
    {
        if ($restaurantIds instanceof EloquentCollection) {
            $restaurantIds = $restaurantIds->pluck('id')->all();
        }
        $this->restaurantIds = $restaurantIds;
    }

    /**
     * @return int|null
     */
    public function getPermissionsRestaurantId()
    {
        return $this->restaurantId;
    }

    /**
     * @return string|null
     */
    public function getPermissionsRestaurantUuid()
    {
        return $this->restaurantUuid;
    }

    /**
     * @return array<int>
     */
    public function getPermissionsRestaurantIds(): array
    {
        return $this->restaurantIds;
    }

    /**
     * @param array $params
     * @param bool $onlyOne
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPermissions(array $params = [], bool $onlyOne = false): EloquentCollection
    {
        $this->loadPermissions();
        $method = $onlyOne ? 'first' : 'filter';

        $permissions = $this->permissions->$method(static function (Permission $permission) use ($params) {
            foreach ($params as $attr => $value) {
                if ($permission->getAttribute($attr) != $value) {
                    return false;
                }
            }

            return true;
        });

        if ($onlyOne) {
            $permissions = new EloquentCollection($permissions ? [$permissions] : []);
        }

        return $permissions;
    }

    /**
     * Flush the cache.
     */
    public function forgetCachedPermissions()
    {
        $this->permissions = null;

        return Cache::forget(self::$cacheKey);
    }

    protected function getPermissionsWithRoles(): EloquentCollection
    {
        return Permission::select()->with('roles')->get();
    }

    /*
     * Make the cache smaller using an array with only required fields
     */
    private function getSerializedPermissionsForCache()
    {
        $permissions = $this->getPermissionsWithRoles()
                            ->map(function (Permission $permission) {
                                if (! $this->alias) {
                                    $this->aliasModelFields($permission);
                                }

                                return $this->aliasedArray($permission) + $this->getSerializedRoleRelation($permission);
                            })
                            ->all();
        $roles = array_values($this->cachedRoles);
        $this->cachedRoles = [];

        return ['alias' => array_flip($this->alias)] + compact('permissions', 'roles');
    }

    /**
     * @param \App\Models\Permission $permission
     */
    private function getSerializedRoleRelation(Permission $permission)
    {
        if (! $permission->roles->count()) {
            return [];
        }

        if (! isset($this->alias['roles'])) {
            $this->alias['roles'] = 'r';
            $this->aliasModelFields($permission->roles[0]);
        }

        return [
            'r' => $permission->roles->map(function (Role $role) {
                if (! isset($this->cachedRoles[$role->getKey()])) {
                    $this->cachedRoles[$role->getKey()] = $this->aliasedArray($role);
                }

                return $role->getKey();
            })->all(),
        ];
    }

    /**
     * Array for cache alias
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    private function aliasModelFields($model): void
    {
        $i = 0;
        $alphas = ! count($this->alias) ? range('a', 'h') : range('j', 'p');

        foreach (array_keys($model->getAttributes()) as $value) {
            if (! isset($this->alias[$value])) {
                $this->alias[$value] = $alphas[$i++] ?? $value;
            }
        }

        $this->alias = array_diff_key($this->alias, array_flip($this->except));
    }

    /**
     * Changes array keys with alias
     * @param array|\Illuminate\Database\Eloquent\Model  $model
     *
     * @return array
     */
    private function aliasedArray($model): array
    {
        return collect(is_array($model) ? $model : $model->getAttributes())->except($this->except)
                    ->keyBy(function ($value, $key) {
                        return $this->alias[$key] ?? $key;
                    })
                    ->all();
    }

    private function loadPermissions()
    {
        if ($this->permissions) {
            return;
        }

        $this->permissions = Cache::remember(self::$cacheKey, self::$cacheExpirationTime, function () {
            return $this->getSerializedPermissionsForCache();
        });

        $this->alias = $this->permissions['alias'];

        $this->hydrateRolesCache();

        $this->permissions = $this->getHydratedPermissionCollection();

        $this->cachedRoles = $this->alias = [];
    }

    private function getHydratedPermissionCollection()
    {
        $permissionInstance = new Permission();

        return EloquentCollection::make(
            array_map(function ($item) use ($permissionInstance) {
                return $permissionInstance
                    ->newFromBuilder($this->aliasedArray(array_diff_key($item, ['r' => 0])))
                    ->setRelation('roles', $this->getHydratedRoleCollection($item['r'] ?? []));
            }, (array)$this->permissions['permissions'])
        );
    }

    private function getHydratedRoleCollection(array $roles)
    {
        return EloquentCollection::make(array_values(
            array_intersect_key($this->cachedRoles, array_flip($roles))
        ));
    }

    private function hydrateRolesCache()
    {
        $roleInstance = new Role();

        array_map(function ($item) use ($roleInstance) {
            $role = $roleInstance->newFromBuilder($this->aliasedArray($item));
            $this->cachedRoles[$role->getKey()] = $role;
        }, (array)$this->permissions['roles']);

        $this->permissions['roles'] = [];
    }
}
