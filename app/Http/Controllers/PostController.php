<?php

namespace App\Http\Controllers;

use Knuckles\Scribe\Attributes\{Authenticated, Endpoint, Group, Header, Response, ResponseFromFile, UrlParam};
use App\Http\Requests\Restaurant\CreatePostRequest;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use App\Contracts\PostContract;
use App\Http\Requests\Restaurant\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\{Restaurant, Post};
use Illuminate\Support\Facades\Gate;

#[Authenticated]
#[Group("Restaurants")]
class PostController extends Controller
{
    #[Endpoint(title: "Create the restaurant post.", description: "The endpoint that the user can create his own restaurant.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[UrlParam(name: "restaurant", type: "string", description: "The uuid of the restaurant.", example: "3315946")]
    #[Response(description: "success", content: "", status: 201)]
    public function create(Restaurant $restaurant, CreatePostRequest $request, PostContract $postContract): HttpResponse
    {
        $user = Auth::user();
        /** @var array<string> */
        $imagePaths = array();
        /** @var array<string> */
        $videoPaths = array();

        if ($request->safe()->has('images')) {
            $imagePaths = $postContract->storePostImages($restaurant->uuid, $request->safe()->__get('images'));
        }

        if ($request->safe()->has('videos')) {
            $videoPaths = $postContract->storePostVideos($restaurant->uuid, $request->safe()->__get('images'));
        }

        $postContract->create(
            $user,
            $restaurant,
            $request->only('title', 'description'),
            $imagePaths,
            $videoPaths
        );

        return response()->noContent(201);
    }

    #[Endpoint(title: "View the restaurant post.", description: "The endpoint that the user can view the restaurant post.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[UrlParam(name: "restaurant", type: "string", description: "The uuid of the restaurant.", example: "3315946")]
    #[UrlParam(name: "post", type: "string", description: "The uuid of the post.", example: "0bad5883-e8d3-44ac-ae3d-a9a1199d8cf4")]
    #[ResponseFromFile(file: "storage/responses/restaurant.post.get.json")]
    public function show(Restaurant $restaurant, Post $post)
    {
        $response = Gate::inspect('restaurant-has-post', [$restaurant, $post]);
        if ($response->allowed()) {
            return new PostResource($post->load(['createdBy', 'updatedBy']));
        } else {
            abort($response->code(), $response->message());
        }
    }

    #[Endpoint(title: "Get posts.", description: "The endpoint that the user can posts that belong to the restaurant.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[UrlParam(name: "restaurant", type: "string", description: "The uuid of the restaurant.", example: "3315946")]
    #[ResponseFromFile(file: "storage/responses/restaurant.posts.get.json")]
    public function getPosts(Restaurant $restaurant, PostContract $postContract)
    {
        return PostResource::collection($postContract->getPosts($restaurant));
    }

    #[Endpoint(title: "Update the restaurant post.", description: "The endpoint that the user can update his own restaurant.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[UrlParam(name: "restaurant", type: "string", description: "The uuid of the restaurant.", example: "3315946")]
    #[UrlParam(name: "post", type: "string", description: "The uuid of the post.", example: "0bad5883-e8d3-44ac-ae3d-a9a1199d8cf4")]
    #[Response(description: "success", content: "", status: 204)]
    public function update(Restaurant $restaurant, Post $post, UpdatePostRequest $request, PostContract $postContract)
    {
        $partialImages = collect();
        $partialVideos = collect();

        if ($request->safe()->has('images')) {
            $imagesRequest = collect($request->safe()->__get('images'));
            $imageFiles = $imagesRequest->pluck('file')->all();
            $postContract->deleteImageFilesOnStorage($imagesRequest->pluck('uuid')->filter()->all());
            $partialImages = collect($postContract->storePostImages($restaurant->uuid, $imageFiles))->map(
                fn ($imagePath, $index)  => ['uuid' => $imagesRequest[$index]['uuid'], 'path' => $imagePath]
            );
        }

        if ($request->safe()->has('videos')) {
            $videosRequest = collect($request->safe()->__get('videos'));
            $videoFiles = collect($videosRequest)->pluck('file')->all();
            $postContract->deleteVideoFilesOnStorage($videosRequest->pluck('uuid')->filter()->all());
            $partialVideos = collect($postContract->storePostVideos($restaurant->uuid, $videoFiles))->map(
                fn ($videoPath, $index)  => ['uuid' => $videosRequest[$index]['uuid'], 'path' => $videoPath]
            );
        }

        $postContract->update(
            Auth::user(),
            $post,
            [
                'title' => $request->safe()->__get('title'),
                'description' => $request->safe()->__get('description'),
            ],
            $partialImages,
            $partialVideos
        );

        return response()->noContent(204);
    }
}
