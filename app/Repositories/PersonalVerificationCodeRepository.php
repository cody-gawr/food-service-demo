<?php

namespace App\Repositories;

use App\Models\PersonalVerificationCode;

class PersonalVerificationCodeRepository
{
    /**
     * @param \App\Models\PersonalVerificationCode $personalVerificationCode
     */
    public function __construct(
        public readonly PersonalVerificationCode $personalVerificationCode
    ) {}

    /**
     * @return string
     */
    public function generate6DigitsCode(): string
    {
        do {
            $code = random_int(100000, 999999);
        } while ($this->personalVerificationCode->where('code', '=', strval($code))->first());

        return $code;
    }

    /**
     * @param string $code
     *
     * @return \App\Models\PersonalVerificationCode|null
     */
    public function findByCode(string $code): ?PersonalVerificationCode
    {
        return $this->personalVerificationCode->where('code', $code)->first();
    }
}
