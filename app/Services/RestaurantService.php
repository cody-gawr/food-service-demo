<?php

namespace App\Services;

use App\Repositories\{RestaurantRepository, RestaurantProfileRepository, ImageRepository, UserRepository, VideoRepository};
use App\Models\{Post, Restaurant,RestaurantProfile, User};
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use App\Contracts\RestaurantContract;

class RestaurantService implements RestaurantContract
{
    /**
     * @param RestaurantRepository $restaurantRepository
     */
    public function __construct(
        public readonly RestaurantRepository $restaurantRepository,
        public readonly RestaurantProfileRepository $restaurantProfileRepository,
        public readonly UserRepository $userRepository,
        public readonly ImageRepository $imageRepository,
        public readonly VideoRepository $videoRepository
    ) {}

    public function findByUuid(string $uuid): Restaurant|null
    {
        return $this->restaurantRepository->findByUuid($uuid);
    }

    /**
     * @param \Illuminate\Support\Collection $keywords
     * @param \Illuminate\Support\Collection $orderBys
     * @param \App\Models\User|null $user
     * @param int $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getRestaurants(BaseCollection $keywords, BaseCollection $orderBys, User $user = null, int $perPage= 10): LengthAwarePaginator
    {
        return $this->restaurantRepository->getRestaurants($keywords, $orderBys, $user, $perPage);
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @return array
     */
    public function subscribe(User $user, Restaurant $restaurant): array
    {
        return $this->restaurantRepository->subscribe($user, $restaurant);
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @return array
     */
    public function unsubscribe(User $user, Restaurant $restaurant): array
    {
        return $this->restaurantRepository->unsubscribe($user, $restaurant);
    }

    public function isSubscribing(User $user, Restaurant $restaurant): bool
    {
        return $this->restaurantRepository->isSubscribing($user, $restaurant);
    }

    /**
     * @param string  $restaurantUuid
     * @param array $documents
     *
     * @return array
     */
    public function storeDocuments(string $restaurantUuid, array $documents): array
    {
        $documentPaths = array();
        foreach($documents as $document) {
            $path = Storage::disk('local')->put("documents/{$restaurantUuid}", $document);
            array_push($documentPaths, $path);
        }

        return $documentPaths;
    }

    /**
     * @param string  $restaurantUuid
     * @param array<\Illuminate\Http\UploadedFile> $images
     *
     * @return array
     */
    public function storeProfileImages(string $restaurantUuid, array $images): array
    {
        $imagePaths = array();
        foreach($images as $image) {
            $path = Storage::disk('public')->put("images/{$restaurantUuid}", $image);
            array_push($imagePaths, $path);
        }

        return $imagePaths;
    }

    /**
     * @param string  $restaurantUuid
     * @param array<\Illuminate\Http\UploadedFile> $videos
     *
     * @return array
     */
    public function storeProfileVideos(string $restaurantUuid, array $videos): array
    {
        $videoPaths = array();
        foreach($videos as $video) {
            $path = Storage::disk('public')->put("videos/{$restaurantUuid}", $video);
            array_push($videoPaths, $path);
        }

        return $videoPaths;
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @param array<string> $documents
     *
     * @return array
     */
    public function claimOwner(User $user, Restaurant $restaurant, array $documents = []): array
    {
        return $this->restaurantRepository->claimOwner($user, $restaurant, $documents);
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @param string  $description
     * @param array<string>  $imagePaths
     * @param array<string>  $videoPaths
     *
     * @return \App\Models\RestaurantProfile
     */
    public function createProfile(User $user, Restaurant $restaurant, string $description, array $imagePaths = [],  array $videoPaths = []): RestaurantProfile
    {
        return $this->restaurantProfileRepository->create(
            $user,
            $restaurant,
            $description,
            $imagePaths,
            $videoPaths
        );
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @param string  $description
     * @param \Illuminate\Support\Collection  $partialImages
     * @param \Illuminate\Support\Collection  $partialVideos
     *
     * @return \App\Models\RestaurantProfile
     */
    public function updateProfile(User $user, Restaurant $restaurant, string $description, BaseCollection $paritalImages, BaseCollection $partialVideos): RestaurantProfile
    {
        return $this->restaurantProfileRepository->updateProfile($user, $restaurant, $description,$paritalImages, $partialVideos);
    }

    /**
     * @param \App\Models\Restaurant
     *
     * @return \App\Models\RestaurantProfile|null
     */
    public function getProfile(Restaurant $restaurant): RestaurantProfile|null
    {
        return $this->restaurantProfileRepository->get($restaurant);
    }

    /**
     * @param array  $uuids
     *
     * @return void
     */
    public function deleteImageFilesOnStorage(array $uuids): void
    {
        foreach($uuids as $uuid)
        {
            $image = $this->imageRepository->findByUuid($uuid);
            if (! is_null($image)) {
                Storage::disk('public')->delete($image->path);
            }
        }
    }

    /**
     * @param array  $uuids
     *
     * @return void
     */
    public function deleteVideoFilesOnStorage(array $uuids): void
    {
        foreach($uuids as $uuid)
        {
            $video = $this->videoRepository->findByUuid($uuid);
            if (! is_null($video)) {
                Storage::disk('public')->delete($video->path);
            }
        }
    }

    /**
     * @param \App\Models\User  $admin
     * @param string  $userUuid
     * @param \App\Models\Restaurant  $restaurant
     *
     * @return void
     */
    public function approve(User $admin, string $userUuid, Restaurant $restaurant): void
    {
        $user = $this->userRepository->findByUuid($userUuid);
        if ($this->hasUser($restaurant, $user)) {
            $this->restaurantRepository->approve($admin, $user, $restaurant);
        } else {
            abort(400, "The user {$user->uuid} does not subscribe the restaurant {$restaurant->uuid}");
        }
    }

    /**
     * @param \App\Models\Restaurant  $restaurant
     * @param \App\Models\User  $user
     *
     * @return bool
     */
    public function hasUser(Restaurant $restaurant, User $user): bool
    {
        return $restaurant->users()->where('users.id', $user->id)->exists();
    }

    /**
     * @param \App\Models\Restaurant  $restaurant
     * @param \App\Models\Post  $post
     *
     * @return bool
     */
    public function hasPost(Restaurant $restaurant, Post $post): bool
    {
        return $restaurant->posts()->where('posts.id', $post->id)->exists();
    }
}
