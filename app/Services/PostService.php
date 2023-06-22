<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as BaseCollection;
use App\Contracts\PostContract;
use App\Models\Post;
use App\Models\Restaurant;
use App\Models\User;
use App\Repositories\ImageRepository;
use App\Repositories\PostRepository;
use App\Repositories\VideoRepository;
use Illuminate\Support\Facades\Storage;

class PostService implements PostContract
{
    public function __construct(
        public readonly PostRepository $postRepository,
        public readonly ImageRepository $imageRepository,
        public readonly VideoRepository $videoRepository
    ){}

    /**
     * @param \App\Models\Restaurant  $restaurant
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPosts(Restaurant $restaurant): EloquentCollection
    {
        $restaurant->load(['posts', 'posts.createdBy', 'posts.updatedBy']);
        return $restaurant->posts;
    }

    /**
     * @param string  $postUuid
     * @param array<\Illuminate\Http\UploadedFile>  $images
     *
     * @return array
     */
    public function storePostImages(string $postUuid, array $images): array
    {
        $imagePaths = array();
        foreach($images as $image) {
            $path = Storage::disk('public')->put("images/{$postUuid}", $image);
            array_push($imagePaths, $path);
        }

        return $imagePaths;
    }

    /**
     * @param string  $postUuid
     * @param array<\Illuminate\Http\UploadedFile>  $images
     *
     * @return array
     */
    public function storePostVideos(string $postUuid, array $videos): array
    {
        $videoPaths = array();
        foreach($videos as $video) {
            $path = Storage::disk('public')->put("videos/{$postUuid}", $video);
            array_push($videoPaths, $path);
        }

        return $videoPaths;
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
    public function create(User $user, Restaurant $restaurant, array $attributes, array $imagePaths = [], array $videoPaths = []): Post
    {
        return $this->postRepository->create($user, $restaurant, $attributes, $imagePaths, $videoPaths);
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
     * @param \App\Models\User  $user
     * @param \App\Models\Post  $post
     * @param array $attributes
     * @param \Illuminate\Support\Collection  $partialImages
     * @param \Illuminate\Support\Collection  $partialVideos
     *
     * @return \App\Models\Post
     */
    public function update(User $user, Post $post, array $attributes, BaseCollection $partialImages, BaseCollection $partialVideos): Post
    {
        return $this->postRepository->update(
            $user,
            $post,
            $attributes,
            $partialImages,
            $partialVideos
        );
    }
}
