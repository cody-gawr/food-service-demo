<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\PersonalVerificationCode;
use Illuminate\Validation\Validator;
use Illuminate\Support\Carbon;
use Closure;

class ValidVerificationCode implements ValidationRule, ValidatorAwareRule
{
    /**
     * The validator instance.
     *
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->validator->validateExists($attribute, $value, ['App\Models\PersonalVerificationCode', 'code'])) {
            $fail('The selected :attribute is invalid.');
        }
        if (PersonalVerificationCode::where('code', $value)->where('expires_at', '<', Carbon::now())->exists()) {
            $fail('The selected :attribute is expired.');
        }
    }

    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }
}
