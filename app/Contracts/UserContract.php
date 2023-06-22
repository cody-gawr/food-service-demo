<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use App\Models\Restaurant;
use App\Models\User;

interface UserContract
{
    public function patch(User $user, array $attributes): bool;

    public function storeAvatar(string $uuid, UploadedFile $avatar): string;

    public function deleteAvatar(string $avatar): bool;

    public function follow(User $user, string $leaderUuid): void;

    public function unfollow(User $user, string $leaderUuid): void;

    public function getUsers(int $id, Collection $keywords): LengthAwarePaginator;

    public function getFollowers(User $user): LengthAwarePaginator;

    /**
     * @param \App\Models\User  $user
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getFollowings(User $user): LengthAwarePaginator;

    /**
     * @param \App\Models\User  $user
     * @param string $roleName
     * @param \App\Models\Restaurant|null  $restaurant
     * @return void
     */
    public function assignRole(User $user, string $roleName, Restaurant $restaurant = null): void;

    /**
     * @param \App\Models\User  $user
     * @param string|int|\App\Contracts\PermissionContract  $permission
     * @param \App\Models\Restaurant  $restaurant
     * @return bool
     */
    public function hasPermissionInRestaurant(User $user, $permission, Restaurant $restaurant): bool;
}
