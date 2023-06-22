<?php

namespace App\Services;

use App\Repositories\PersonalVerificationCodeRepository;
use App\Models\PersonalVerificationCode;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Events\Auth\PasswordForgot;
use App\Events\Auth\UserRegistered;
use App\Contracts\AuthContract;
use Illuminate\Support\Carbon;
use App\Models\User;

class AuthService implements AuthContract
{
    /**
     * @param UserRepository $userRepository
     * @param PersonalVerificationCodeRepository $personalVerificationCodeRepository
     */
    public function __construct(
        public readonly UserRepository $userRepository,
        public readonly PersonalVerificationCodeRepository $personalVerificationCodeRepository
    ) {}

    public function create6DigitsCode(): string
    {
        do {
            $code = random_int(100000, 999999);
        } while (PersonalVerificationCode::where("code", "=", strval($code))->first());

        return $code;
    }

    public function createToken(array $credentials, string $tokenType): string
    {
        $message = 'Email|User Name or Password is incorrect.';
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->hasVerifiedEmail()) {
                return $user->createToken($tokenType, expiresAt: Carbon::now()->addMinutes(config('sanctum.expiration')))->plainTextToken;
            } else {
                $message = 'You must verify your email.';
            }
        }

        abort(400, $message);
    }

    public function revokeTokens(User $user): bool
    {
        return $user->tokens()->delete();
    }

    public function register(array $attributes): User
    {
        $user = $this->userRepository->create($attributes);
        event(new UserRegistered($user));

        return $user;
    }

    public function verify(string $code): bool
    {
        $personalVerficationCode = $this->personalVerificationCodeRepository->findByCode($code);
        if (is_null($personalVerficationCode)) {
            abort(400, 'Invalid Code.');
        }

        $personalVerficationCode->user->markEmailAsVerified();
        return $personalVerficationCode->delete();
    }

    /**
     * @param string $email
     *
     * @return void
     */
    public function sendVerificationCodeForResettingPassword(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if (is_null($user)) {
            abort(400, 'Invalid Email.');
        }

        event(new PasswordForgot($user));
    }

    public function resetPassword(string $code, string $password): bool
    {
        $personalVerficationCode = $this->personalVerificationCodeRepository->findByCode($code);
        $personalVerficationCode->user->update([
            'password' => $password
        ]);

        return $personalVerficationCode->delete();
    }
}
