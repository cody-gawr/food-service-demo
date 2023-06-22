<?php

namespace App\Http\Controllers;

use Knuckles\Scribe\Attributes\{Authenticated, Endpoint, Group, Response, ResponseFromFile, UrlParam};
use App\Http\Requests\Restaurant\GetRestaurantsRequest;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response as HttpResponse;
use App\Http\Resources\RestaurantCollection;
use App\Http\Requests\ClaimOwnerRequest;
use App\Contracts\RestaurantContract;
use App\Http\Requests\Restaurant\ApproveRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Restaurant;

#[Authenticated]
#[Group("Restaurants")]
class RestaurantController extends Controller
{
    #[Authenticated(false)]
    #[Endpoint(title: "Get restaurants.", description: "The endpoint of getting paginated restaurants.")]
    #[ResponseFromFile('storage/responses/restaurants.get.json')]
    /**
     * @param \App\Http\Requests\Restaurant\GetRestaurantsRequest $request
     * @param RestaurantContract $restaurantContract
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index(GetRestaurantsRequest $request, RestaurantContract $restaurantContract): ResourceCollection
    {
        $filterableColumns = ['name', 'address', 'types', 'rating'];
        $sortableColumns = ['name', 'address', 'rating'];
        $keywords = collect($request->get('keyword'))->only($filterableColumns);
        $orderBys = collect($request->get('order_by'))->only($sortableColumns);

        return new RestaurantCollection($restaurantContract->getRestaurants($keywords, $orderBys, Auth::guard('sanctum')->user()));
    }

    #[Endpoint(title: "Subscribe the restaurant.", description: "The endpoint of letting the user subscribe his desired restaurant.")]
    #[UrlParam(name: "restaurant", type: "string", description: "The uuid of the restaurant.", example: "3315946")]
    #[Response(description: "success", content: "", status: 201)]
    public function subscribe(Restaurant $restaurant, RestaurantContract $restaurantContract): HttpResponse
    {
        $user = Auth::user();
        if ($restaurantContract->isSubscribing($user, $restaurant)) {
            abort(400, "The user {$user->uuid} is already subscribing the restaurant {$restaurant->uuid}.");
        }
        $restaurantContract->subscribe($user, $restaurant);

        return response()->noContent(201);
    }

    #[Endpoint(title: "Unsubscribe the restaurant.", description: "The endpoint of letting the user unsubscribe his desired restaurant.")]
    #[UrlParam(name: "restaurant", type: "string", description: "The uuid of the restaurant.", example: "3315946")]
    #[Response(description: "success", content: "", status: 204)]
    public function unsubscribe(Restaurant $restaurant, RestaurantContract $restaurantContract): HttpResponse
    {
        $user = Auth::user();
        if (! $restaurantContract->isSubscribing($user, $restaurant)) {
            abort(400, "The user {$user->uuid} is not subscribing the restaurant {$restaurant->uuid}.");
        }
        $restaurantContract->unsubscribe($user, $restaurant);

        return response()->noContent(204);
    }

    #[Endpoint(title: "Claim owner.", description: "The endpoint that the user can claim he is an owner.")]
    #[UrlParam(name: "restaurant", type: "string", description: "The uuid of the restaurant.", example: "3315946")]
    #[Response(description: "success", content: "", status: 204)]
    public function claimOwner(Restaurant $restaurant, ClaimOwnerRequest $request, RestaurantContract $restaurantContract): HttpResponse
    {
        $documents = $restaurantContract->storeDocuments($restaurant->uuid, $request->safe()->__get('documents'));
        $restaurantContract->claimOwner(Auth::user(), $restaurant, $documents);

        return response()->noContent(204);
    }

    #[Endpoint(title: "Approve owner.", description: "The endpoint that the admin approves of the owner.")]
    #[UrlParam(name: "restaurant", type: "string", description: "The uuid of the restaurant.", example: "3315946")]
    #[Response(description: "success", content: "", status: 204)]
    public function approve(Restaurant $restaurant, ApproveRequest $request, RestaurantContract $restaurantContract): HttpResponse
    {
        $restaurantContract->approve(Auth::user(), $request->safe()->__get('user_uuid'), $restaurant);

        return response()->noContent(204);
    }
}
