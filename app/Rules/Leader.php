<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Repositories\UserRepository;
use App\Models\User;
use Closure;

class Leader implements ValidationRule
{
    public function __construct(
        public readonly User|null $user
    ) {}
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var \App\Repositories\UserRepository */
        $userRepository = app(UserRepository::class);
        $leader = $userRepository->findByUuid($value);
        if (is_null($leader)) {
            $fail('The selected :attribute is invalid.');
        }

        $isFollowing = $userRepository->isFollowing($this->user, $leader);
        if (!$isFollowing) {
            $fail('The user is not following.');
        }

    }
}
