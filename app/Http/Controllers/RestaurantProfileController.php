<?php

namespace App\Http\Controllers;

use Knuckles\Scribe\Attributes\{Authenticated, Endpoint, Group, Header, Response, ResponseFromFile, UrlParam};
use App\Http\Requests\Restaurant\{CreateRestaurantProfileRequest, UpdateRestaurantProfileRequest};
use App\Http\Resources\RestaurantProfileResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response as HttpResponse;
use App\Contracts\RestaurantContract;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\Restaurant;

#[Authenticated]
#[Group("Restaurants")]
class RestaurantProfileController extends Controller
{
    #[Endpoint(title: "Create the restaurant profile.", description: "The endpoint that the user can create his own restaurant profile.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[UrlParam(name: "restaurant", type: "string", description: "The uuid of the restaurant.", example: "3315946")]
    #[Response(description: "success", content: "", status: 201)]
    public function create(Restaurant $restaurant, CreateRestaurantProfileRequest $request, RestaurantContract $restaurantContract): HttpResponse
    {
        $user = Auth::user();
        /** @var array<string> */
        $imagePaths = array();
        /** @var array<string> */
        $videoPaths = array();

        if ($request->safe()->has('images')) {
            $response = Gate::inspect('unlock-restaurant-profile-with-image', [$restaurant]);
            if ($response->allowed()) {
                $imagePaths = $restaurantContract->storeProfileImages($restaurant->uuid, $request->safe()->__get('images'));
            } else {
                abort($response->code(), $response->message());
            }
        }

        if ($request->safe()->has('videos')) {
            $response = Gate::inspect('unlock-restaurant-profile-with-video', [$restaurant]);
            if ($response->allowed()) {
                $videoPaths = $restaurantContract->storeProfileVideos($restaurant->uuid, $request->safe()->__get('videos'));
            } else {
                abort($response->code(), $response->message());
            }
        }

        $restaurantContract->createProfile(
            $user,
            $restaurant,
            $request->safe()->__get('description'),
            $imagePaths,
            $videoPaths
        );

        return response()->noContent(201);
    }

    #[Endpoint(title: "Update the restaurant profile.", description: "The endpoint that the user can update his own restaurant profile.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[UrlParam(name: "restaurant", type: "string", description: "The uuid of the restaurant.", example: "3315946")]
    #[Response(description: "success", content: "", status: 204)]
    public function update(Restaurant $restaurant, UpdateRestaurantProfileRequest $request, RestaurantContract $restaurantContract): HttpResponse
    {
        $user = Auth::user();
        $partialImages = collect();
        $partialVideos = collect();

        if ($request->safe()->has('images')) {
            $response = Gate::inspect('unlock-restaurant-profile-with-image', [$restaurant]);
            if ($response->allowed()) {
                $imagesRequest = collect($request->safe()->__get('images'));
                $imageFiles = $imagesRequest->pluck('file')->all();
                $restaurantContract->deleteImageFilesOnStorage($imagesRequest->pluck('uuid')->filter()->all());
                $partialImages = collect($restaurantContract->storeProfileImages($restaurant->uuid, $imageFiles))->map(
                    fn ($imagePath, $index)  => ['uuid' => $imagesRequest[$index]['uuid'], 'path' => $imagePath]
                );
            } else {
                abort($response->code(), $response->message());
            }
        }

        if ($request->safe()->has('videos')) {
            $response = Gate::inspect('unlock-restaurant-profile-with-video', [$restaurant]);
            if ($response->allowed()) {
                $videosRequest = collect($request->safe()->__get('videos'));
                $videoFiles = collect($videosRequest)->pluck('file')->all();
                $restaurantContract->deleteVideoFilesOnStorage($videosRequest->pluck('uuid')->filter()->all());
                $partialVideos = collect($restaurantContract->storeProfileVideos($restaurant->uuid, $videoFiles))->map(
                    fn ($videoPath, $index)  => ['uuid' => $videosRequest[$index]['uuid'], 'path' => $videoPath]
                );
            } else {
                abort($response->code(), $response->message());
            }
        }

        $restaurantContract->updateProfile(
            $user,
            $restaurant,
            $request->safe()->__get('description'),
            $partialImages,
            $partialVideos
        );

        return response()->noContent(204);
    }

    #[Endpoint(title: "View profile.", description: "The endpoint that the user can view the profile of the restaurant.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[UrlParam(name: "restaurant", type: "string", description: "The uuid of the restaurant.", example: "3315946")]
    #[ResponseFromFile('storage/responses/restaurant.profile.get.json')]
    #[ResponseFromFile('storage/responses/restaurant.profile.not_found.json', status: 404)]
    public function show(Restaurant $restaurant, RestaurantContract $restaurantContract): JsonResource
    {
        $profile = $restaurantContract->getProfile($restaurant);
        if (is_null($profile)) {
            abort(404, "The restaurant {$restaurant->uuid} has no profile.");
        }
        return new RestaurantProfileResource($profile);
    }
}
