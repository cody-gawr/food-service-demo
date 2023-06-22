<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\{EmailVerificationRequest, ForgotPasswordRequest, ResetPasswordRequest, RegisterRequest, LoginRequest};
use Knuckles\Scribe\Attributes\{Endpoint, Group, Response, Header};
use Illuminate\Http\Response as HttpResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Contracts\AuthContract;
use App\Contracts\RestaurantContract;
use App\Contracts\UserContract;

#[Group("Authentication", "APIs for authentication actions such as login, register")]
class AuthController extends Controller
{
    #[Endpoint("Login Endpoint")]
    #[Response(description: "success", content: [
        "api_token" => "1|Xe8vOfwFkoUCeW6w35UnIwwuP4YMvd2EW97qMXFW"
    ])]
    /**
     * @param \App\Http\Requests\Auth\LoginRequest  $loginRequest
     * @param \App\Contracts\AuthContract  $authContract
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request, AuthContract $authContract): \Illuminate\Http\JsonResponse
    {
        $credentials = $request->has('email')
            ? $request->only(['email', 'password'])
            : $request->only(['name', 'password']);
        $tokenType = $request->get('platform') . '-token';
        $token = $authContract->createToken($credentials, $tokenType);

        return response()->json(['api_token' => $token]);
    }

    #[Endpoint("Logout Endpoint")]
    #[Header("Authorization", "Bearer issued_api_token")]
    #[Response(description: "success", status: 204, content: "")]
    /**
     * @param \App\Contracts\AuthContract  $authContract
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(AuthContract $authContract): HttpResponse
    {
        $authContract->revokeTokens(Auth::user());

        return response()->noContent();
    }

    #[Endpoint("Register Endpoint")]
    #[Response(description: "success", status: 201, content: "")]
    /**
     * @param \App\Http\Requests\Auth\RegisterRequest  $registerRequest
     * @param \App\Contracts\AuthContract  $authContract
     *
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request, UserContract $userContract, AuthContract $authContract, RestaurantContract $restaurantContract)//: HttpResponse
    {
        $attributes = $request->except('avatar', 'password_confirmation');
        if ($request->safe()->has('avatar')) {
            $attributes = [...$attributes, 'avatar' => $userContract->storeAvatar('temporary', $request->avatar)];
        }
        $user = $authContract->register($attributes);

        if ($request->safe()->has('role')) {
            /** @var \App\Models\Restaurant */
            $restaurant = $restaurantContract->findByUuid($request->safe()->__get('restaurant_uuid'));
            // $userContract->assignRole($user, $request->safe()->__get('role'), $restaurant);
            // $documents = $restaurantContract->storeDocuments($restaurant->uuid, $request->safe()->__get('documents'));
            $restaurantContract->claimOwner($user, $restaurant);
        }

        return response()->noContent(201);
    }


    #[Endpoint("Verification Endpoint")]
    #[Response(description: "success", status: 204, content: "")]
    /**
     * @param \App\Http\Requests\Auth\EmailVerificationRequest  $request
     * @param \App\Contracts\AuthContract  $authContract
     *
     * @return \Illuminate\Http\Response
     */
    public function verify(EmailVerificationRequest $request, AuthContract $authContract): HttpResponse
    {
        $verificationCode = $request->verification_code;
        $authContract->verify($verificationCode);

        return response()->noContent();
    }

    #[Endpoint(
        title: "Forgot Password Endpoint",
        description: "The endpoint of issueing 6 digit verfication code.",
        authenticated: false
    )]
    #[Response(description: "success", status: 204, content: "")]
    /**
     * @param \App\Http\Requests\Auth\ForgotPasswordRequest  $request
     * @param \App\Contracts\AuthContract  $authContract
     *
     * @return \Illuminate\Http\Response
     */
    public function sendVerificationCode(ForgotPasswordRequest $request, AuthContract $authContract): HttpResponse
    {
        $authContract->sendVerificationCodeForResettingPassword($request->safe()->__get('email'));

        return response()->noContent();
    }

    #[Endpoint(
        title: "Reset Password Endpoint",
        description: "The endpoint of resetting a password with an issued 6 digit verification code.",
        authenticated: false
    )]
    #[Response(description: "success", status: 204, content: "")]
    /**
     * @param \App\Http\Requests\Auth\ResetPasswordRequest  $request
     * @param \App\Contracts\AuthContract  $authContract
     *
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(ResetPasswordRequest $request, AuthContract $authContract): HttpResponse
    {
        $authContract->resetPassword($request->safe()->__get('code'), $request->safe()->__get('password'));
        return response()->noContent();
    }
}
