<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use App\Repositories\UserRepository;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use App\Contracts\UserContract;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserService implements UserContract
{
    /**
     * @param \App\Repositories\UserRepository $userRepository
     */
    public function __construct(
        public readonly UserRepository $userRepository
    ) {}

    /**
     * @param \Illuminate\Http\UploadedFile $avatar
     *
     * @return string
     */
    public function storeAvatar(string $uuid, UploadedFile $avatar): string
    {
        return Storage::disk('public')->put("avatars/{$uuid}", $avatar);
    }

    /**
     * @param string $avatar
     *
     * @return bool
     */
    public function deleteAvatar(string $avatarPath): bool
    {
        $isDeleted = false;
        if (Storage::disk('public')->exists($avatarPath)) {
            $isDeleted = Storage::disk('public')->delete($avatarPath);
        }

        return $isDeleted;
    }

    /**
     * @param \App\Models\User
     * @param array $attributes
     *
     * @return bool
     */
    public function patch(User $user, array $attributes): bool
    {
        if (array_key_exists('avatar', $attributes)) {
            $this->deleteAvatar($user->avatar);
        }

        $isPatched = $this->userRepository->update($user, $attributes);
        if ($isPatched && array_key_exists('email', $attributes)) {
            // NewEmail Event
        }

        return $isPatched;
    }

    public function follow(User $user, string $leaderUuid): void
    {
        $leader = $this->userRepository->findByUuid($leaderUuid);
        $this->userRepository->follow($user, $leader);
    }

    public function unfollow(User $user, string $leaderUuid): void
    {
        $leader = $this->userRepository->findByUuid($leaderUuid);
        $this->userRepository->unfollow($user, $leader);
    }

    /**
     * @param int $userId
     * @param \Illuminate\Support\Collection $keywords
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUsers(int $userId, Collection $keywords): LengthAwarePaginator
    {
        return $this->userRepository->getUsers($userId, $keywords);
    }

    public function getFollowers(User $user): LengthAwarePaginator
    {
        return $this->userRepository->getFollowers($user);
    }

    public function getFollowings(User $user): LengthAwarePaginator
    {
        return $this->userRepository->getFollowings($user);
    }

    /**
     * @param \App\Models\User  $user
     * @param string $roleName
     * @param \App\Models\Restaurant|null  $restaurant
     * @return void
     */
    public function assignRole(User $user, string $roleName, Restaurant $restaurant = null): void
    {
        if (! is_null($restaurant)) {
            setPermissionsRestaurantIdAndUuid($restaurant);
        }
        $user->assignRole($roleName);
    }

    /**
     * @param \App\Models\User  $user
     * @param string|int|\App\Contracts\PermissionContract  $permission
     * @param \App\Models\Restaurant  $restaurant
     * @return bool
     */
    public function hasPermissionInRestaurant(User $user, $permission, Restaurant $restaurant): bool
    {
        /** @var \App\Models\Permission */
        $permission = $user->filterPermission($permission);

        return $user->hasRole('admin') ||
            $user->loadMissing(
                [
                    'roles' => function (BelongsToMany $query) use ($restaurant) {
                        $query->wherePivot('restaurant_id', $restaurant->id);
                    },
                    'roles.permissions' => function (BelongsToMany $query) use ($permission) {
                        $query->where('id', $permission->id);
                    }
                ]
            )->roles->flatMap(function ($role) {
                return $role->permissions;
            })
            ->sort()
            ->values()
            ->contains(
                $permission->getKeyName(),
                $permission->getKey()
            );
    }
}
