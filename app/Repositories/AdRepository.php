<?php

namespace App\Repositories;

use App\Models\Ad;

class AdRepository
{
    public function __construct(
        public readonly Ad $ad
    ) {}

    /**
     * @param array  $attributes
     *
     * @return \App\Models\Ad
     */
    public function create(array $attributes): Ad
    {
        $postInstance = $this->ad->newInstance();
        $postInstance->fill($attributes);
        $postInstance->save();

        return $postInstance;
    }

    /**
     * @param \App\Models\Ad  $ad
     * @param array $attributes
     *
     * @return bool
     */
    public function update(Ad $ad, array $attributes): bool
    {
        return $ad->update($attributes);
    }
}
