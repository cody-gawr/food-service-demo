<?php

namespace App\Http\Controllers;

use Knuckles\Scribe\Attributes\{Authenticated, Endpoint, Group, Header, Response, ResponseFromApiResource, ResponseFromFile};
use App\Http\Requests\User\{UnfollowRequest, PatchUserRequest, FollowRequest, GetUsersRequest};
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response as HttpResponse;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Contracts\UserContract;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Authenticated]
#[Group("User Management")]
class UserController extends Controller
{
    #[Endpoint("Get users.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[ResponseFromFile(file: "storage/responses/users.get.json")]
    /**
     * @param \App\Http\Requests\User\GetUsersRequest $request
     * @param \App\Contracts\UserContract $userContract
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function getUsers(GetUsersRequest $request, UserContract $userContract): ResourceCollection
    {
        $filterableColumns = ['first_name', 'last_name', 'name', 'address'];
        $keywords = collect($request->get('keyword'))->only($filterableColumns);

        return new UserCollection($userContract->getUsers(Auth::user()->id, $keywords));
    }

    #[Endpoint("View the profile of the user.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[ResponseFromFile(file: "storage/responses/user.get.json")]
    /**
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(): \Illuminate\Http\Resources\Json\JsonResource
    {
        return new UserResource(
            Auth::user()
                ->load('roles.permissions')
                ->loadCount(['followers', 'followings'])
        );
    }

    #[Endpoint("Update the user.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[Response(description: "success", content: "", status: 204)]
    /**
     * @param \App\Http\Requests\User\PatchUserRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(PatchUserRequest $request, UserContract $userContract): HttpResponse
    {
        $user = Auth::user();
        $attributes = $request->safe()->except('avatar', 'password_confirmation');
        if ($request->has('avatar')) {
            $attributes = [...$attributes, 'avatar' => $userContract->storeAvatar($user->uuid, $request->avatar)];
        }
        if ($userContract->patch($user, $attributes)) {
            return response()->noContent();
        } else {
            abort(400, 'User is not updated.');
        }
    }

    #[Endpoint("Follow the user.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[Response(description: "success", content: "", status: 204)]
    /**
     * @param \App\Http\Requests\User\FollowRequest $rquest
     * @param \App\Contracts\UserContract $userContract
     *
     * @return \Illuminate\Http\Response
     */
    public function follow(FollowRequest $request, UserContract $userContract): HttpResponse
    {
        $leaderUuid = $request->uuid;
        $user = Auth::user();
        $userContract->follow($user, $leaderUuid);

        return response()->noContent();
    }

    #[Endpoint("Unfollow the user.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[Response(description: "success", content: "", status: 204)]
    /**
     * @param \App\Http\Requests\User\UnfollowRequest $rquest
     * @param \App\Contracts\UserContract $userContract
     *
     * @return \Illuminate\Http\Response
     */
    public function unfollow(UnfollowRequest $request, UserContract $userContract)
    {
        $leaderUuid = $request->uuid;
        $user = Auth::user();

        $userContract->unfollow($user, $leaderUuid);

        return response()->noContent();
    }

    #[Endpoint("Get followers.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[ResponseFromApiResource(UserCollection::class, User::class)]
    /**
     * @param UserContract $userContract
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function followers(UserContract $userContract): ResourceCollection
    {
        return new UserCollection($userContract->getFollowers(Auth::user()));
    }

    #[Endpoint("Get followings.")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[ResponseFromApiResource(UserCollection::class, User::class)]
    /**
     * @param UserContract $userContract
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function followings(UserContract $userContract): ResourceCollection
    {
        return new UserCollection($userContract->getFollowings(Auth::user()));
    }

    public function getNotifications()
    {
        $user = Auth::user();
        $user->load(['notifications' => function (BelongsToMany $query) {
            $query->wherePivotNull('read_at');
        }, 'notifications.notifiable']);

        return response()->json(['notifications' => $user->notifications]);
    }
}
