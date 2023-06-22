<?php

namespace App\Repositories;

use Illuminate\Support\Collection as BaseCollection;
use App\Models\Post;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Str;

class PostRepository
{
    public function __construct(
        public readonly Post $post
    ) {}

    /**
     * @param string  $uuid
     *
     * @return \App\Models\Post|null
     */
    public function findByUuid(string $uuid): Post|null
    {
        return $this->post->where('uuid', $uuid)->first();
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @param array  $attributes
     * @param array<string>  $imagePaths
     * @param array<string>  $videoPaths
     *
     * @return \App\Models\Post
     */
    public function create(User $user, Restaurant $restaurant, array $attributes, array $imagePaths, array $videoPaths): Post
    {
        /** @var \App\Models\Post */
        $post = $restaurant->posts()->create([
            'uuid' => Str::uuid()->toString(),
            ...$attributes,
            'restaurant_uuid' => $restaurant->uuid,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        if (count($imagePaths) > 0) {
            $images = collect($imagePaths)->map(fn ($path) => [
                'uuid' => Str::uuid()->toString(),
                'imageable_uuid' => $post->uuid,
                'path' => $path,
            ]);
            $post->images()->createMany($images->all());
        }

        if (count($videoPaths) > 0) {
            $videos = collect($videoPaths)->map(fn ($path) => [
                'uuid' => Str::uuid()->toString(),
                'videoable_uuid' => $post->uuid,
                'path' => $path,
            ]);
            $post->videos()->createMany($videos->all());
        }

        return $post;
    }

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Post  $post
     * @param array $attributes
     * @param \Illuminate\Support\Collection  $partialImages
     * @param \Illuminate\Support\Collection  $partialVideos
     *
     * @return \App\Models\Post
     */
    public function update(User $user, Post $post, array $attributes, BaseCollection $partialImages,  BaseCollection $partialVideos): Post
    {
        $post->update([
            'updated_by' => $user->id,
            ...$attributes
        ]);

        if ($partialImages->isNotEmpty()) {
            $post->images()->whereNotIn('uuid', $partialImages->pluck('uuid')->filter()->all())->delete();
            $partialImages->each(function ($partial) use ($post) {
                if (is_null($partial['uuid'])) {
                    $post->images()->create(['path' => $partial['path'], 'imageable_uuid' => $post->uuid]);
                } else {
                    $post->images()
                        ->where('uuid', $partial['uuid'])
                        ->update([
                            'path' => $partial['path'],
                        ]);
                }
            });
        }

        if ($partialVideos->isNotEmpty()) {
            $post->videos()->whereNotIn('uuid', $partialVideos->pluck('uuid')->filter()->all())->delete();
            $partialVideos->each(function ($partial) use ($post) {
                if (is_null($partial['uuid'])) {
                    $post->videos()->create(['path' => $partial['path'], 'videoable_uuid' => $post->uuid]);
                } else {
                    $post->videos()
                        ->where('uuid', $partial['uuid'])
                        ->update([
                            'path' => $partial['path'],
                        ]);
                }
            });
        }

        return $post;
    }
}
