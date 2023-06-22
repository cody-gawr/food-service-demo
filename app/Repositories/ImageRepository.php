<?php

namespace App\Repositories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Collection;

class ImageRepository
{
    public function __construct(
        public readonly Image $image
    ) {}

    public function findByUuid(string $uuid)
    {
        return $this->image->where('uuid', $uuid)->first();
    }

    /**
     * @param array  $uuids
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get(array $uuids): Collection
    {
        return $this->image->whereIn('uuid', $uuids)->get();
    }
}
