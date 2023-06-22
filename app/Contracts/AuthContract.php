<?php

namespace App\Contracts;

use App\Models\User;

interface AuthContract
{
    public function create6DigitsCode(): string;

    public function createToken(array $credential, string $tokenName): string;

    public function revokeTokens(User $user): bool;

    public function register(array $attributes): User;

    public function verify(string $code): bool;

    public function sendVerificationCodeForResettingPassword(string $email): void;

    public function resetPassword(string $code, string $password): bool;
}
