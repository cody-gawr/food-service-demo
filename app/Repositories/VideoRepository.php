<?php

namespace App\Repositories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;

class VideoRepository
{
    public function __construct(
        public readonly Video $video
    ) {}

    public function findByUuid(string $uuid)
    {
        return $this->video->where('uuid', $uuid)->first();
    }

    /**
     * @param array  $uuids
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get(array $uuids): Collection
    {
        return $this->video->whereIn('uuid', $uuids)->get();
    }
}
