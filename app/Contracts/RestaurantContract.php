<?php

namespace App\Contracts;

use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\RestaurantProfile;
use App\Models\Restaurant;
use App\Models\Post;
use App\Models\User;

interface RestaurantContract
{
    public function findByUuid(string $uuid): Restaurant|null;
    /**
     * @param \Illuminate\Support\Collection $keywords
     * @param \Illuminate\Support\Collection $orderBys
     * @param \App\Models\User|null $user
     * @param int $perPage
     */
    public function getRestaurants(BaseCollection $keywords, BaseCollection $orderBys, User $user = null, int $perPage = 10): LengthAwarePaginator;

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @return array
     */
    public function subscribe(User $user, Restaurant $restaurant): array;

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @return array
     */
    public function unsubscribe(User $user, Restaurant $restaurant): array;

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @return bool
     */
    public function isSubscribing(User $user, Restaurant $restaurant): bool;

    /**
     * @param string  $restaurantUuid
     * @param array<\Illuminate\Http\UploadedFile>  $documents
     * @return array<string>
     */
    public function storeDocuments(string $restaurantUuid, array $documents): array;

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @param array<string>  $documents
     * @return array
     */
    public function claimOwner(User $user, Restaurant $restaurant, array $documents = []): array;

    /**
     * @param string  $restaurantUuid
     * @param array<\Illuminate\Http\UploadedFile>  $images
     * @return array<string>
     */
    public function storeProfileImages(string $restaurantUuid, array $images): array;

    /**
     * @param string  $restaurantUuid
     * @param array<\Illuminate\Http\UploadedFile>  $videos
     * @return array<string>
     */
    public function storeProfileVideos(string $restaurantUuid, array $videos): array;

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @param string  $description
     * @param array<string>  $imagePaths
     * @param array<string>  $videoPaths
     *
     * @return \App\Models\RestaurantProfile
     */
    public function createProfile(User $user, Restaurant $restaurant, string $description, array $imagePaths = [], array $videoPaths = []): RestaurantProfile;

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @param string  $description
     * @param array<string>  $imagePaths
     * @param array<string>  $videoPaths
     *
     * @return \App\Models\RestaurantProfile
     */
    public function updateProfile(User $user, Restaurant $restaurant, string $description, BaseCollection $partialImages, BaseCollection $partialVideos): RestaurantProfile;

    /**
     * @param \App\Models\Restaurant
     *
     * @return \App\Models\RestaurantProfile|null
     */
    public function getProfile(Restaurant $restaurant): RestaurantProfile|null;

    /**
     * @param array  $uuids
     *
     * @return void
     */
    public function deleteImageFilesOnStorage(array $uuids): void;

    /**
     * @param array  $uuids
     *
     * @return void
     */
    public function deleteVideoFilesOnStorage(array $uuids): void;

    /**
     * @param \App\Models\User  $admin
     * @param string  $userUuid
     * @param \App\Models\Restaurant  $restaurant
     *
     * @return void
     */
    public function approve(User $admin, string $userUuid, Restaurant $restaurant): void;

    /**
     * @param \App\Models\Restaurant  $restaurant
     * @param \App\Models\User  $user
     *
     * @return bool
     */
    public function hasUser(Restaurant $restaurant, User $user): bool;

    /**
     * @param \App\Models\Restaurant  $restaurant
     * @param \App\Models\Post  $post
     *
     * @return bool
     */
    public function hasPost(Restaurant $restaurant, Post $post): bool;
}
