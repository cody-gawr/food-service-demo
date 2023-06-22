<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as BaseCollection;
use App\Models\{Restaurant, User, Post};

interface PostContract
{
    /**
     * @param \App\Models\Restaurant  $restaurant
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPosts(Restaurant $restaurant): EloquentCollection;
    /**
     * @param string  $postUuid
     * @param array<\Illuminate\Http\UploadedFile>  $images
     *
     * @return array
     */
    public function storePostImages(string $postUuid, array $images): array;

    /**
     * @param string  $postUuid
     * @param array<\Illuminate\Http\UploadedFile>  $images
     *
     * @return array
     */
    public function storePostVideos(string $postUuid, array $images): array;

     /**
     * @param array  $uuids
     *
     * @return void
     */
    public function deleteVideoFilesOnStorage(array $uuids): void;

    /**
     * @param array  $uuids
     *
     * @return void
     */
    public function deleteImageFilesOnStorage(array $uuids): void;

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Restaurant  $restaurant
     * @param array  $attributes
     * @param array<string>  $imagePaths
     * @param array<string>  $videoPaths
     *
     * @return \App\Models\Post
     */
    public function create(User $user, Restaurant $restaurant, array $attributes, array $imagePaths = [], array $videoPaths = []): Post;

    /**
     * @param \App\Models\User  $user
     * @param \App\Models\Post  $post
     * @param array $attributes
     * @param \Illuminate\Support\Collection  $partialImages
     * @param \Illuminate\Support\Collection  $partialVideos
     *
     * @return \App\Models\Post
     */
    public function update(User $user, Post $post, array $attributes, BaseCollection $partialImages,  BaseCollection $partialVideos): Post;
}
