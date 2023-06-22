<?php

namespace App\Repositories;

use App\Models\{RestaurantProfile, Restaurant, User};
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Str;

class RestaurantProfileRepository
{
    public function __construct(
        public readonly RestaurantProfile $restaurantProfile
    ) {}

    /**
     * @param \App\Models\Restaurant
     *
     * @return \App\Models\RestaurantProfile|null
     */
    public function get(Restaurant $restaurant): RestaurantProfile|null
    {
        return $restaurant->profile ? $restaurant->profile->load(['images', 'videos']) : null;
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
    public function create(User $user, Restaurant $restaurant, string $description, array $imagePaths,  array $videoPaths): RestaurantProfile
    {
        /** @var \App\Models\RestaurantProfile */
        $profile = $restaurant->profile()->create([
            'description' => $description,
            'restaurant_uuid' => $restaurant->uuid,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        if (count($imagePaths) > 0) {
            $images = collect($imagePaths)->map(fn ($path) => [
                'uuid' => Str::uuid()->toString(),
                'imageable_uuid' => $profile->uuid,
                'path' => $path,
            ]);
            $profile->images()->createMany($images->all());
        }

        if (count($videoPaths) > 0) {
            $videos = collect($videoPaths)->map(fn ($path) => [
                'uuid' => Str::uuid()->toString(),
                'videoable_uuid' => $profile->uuid,
                'path' => $path,
            ]);
            $profile->videos()->createMany($videos->all());
        }

        return $profile;
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
    public function updateProfile(User $user, Restaurant $restaurant, string $description, BaseCollection $partialImages,  BaseCollection $partialVideos): RestaurantProfile
    {
        $restaurant->profile()->update([
            'description' => $description,
            'updated_by' => $user->id,
        ]);
        /** @var \App\Models\RestaurantProfile */
        $profile = $restaurant->profile;

        if ($partialImages->isNotEmpty()) {
            $profile->images()->whereNotIn('uuid', $partialImages->pluck('uuid')->filter()->all())->delete();
            $partialImages->each(function ($partial) use ($profile) {
                if (is_null($partial['uuid'])) {
                    $profile->images()->create(['path' => $partial['path'], 'imageable_uuid' => $profile->uuid]);
                } else {
                    $profile->images()
                        ->where('uuid', $partial['uuid'])
                        ->update([
                            'path' => $partial['path'],
                        ]);
                }
            });
        }

        if ($partialVideos->isNotEmpty()) {
            $profile->videos()->whereNotIn('uuid', $partialVideos->pluck('uuid')->filter()->all())->delete();
            $partialVideos->each(function ($partial) use ($profile) {
                if (is_null($partial['uuid'])) {
                    $profile->videos()->create(['path' => $partial['path'], 'videoable_uuid' => $profile->uuid]);
                } else {
                    $profile->videos()
                        ->where('uuid', $partial['uuid'])
                        ->update([
                            'path' => $partial['path'],
                        ]);
                }
            });
        }

        return $profile;
    }
}
