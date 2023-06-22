<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'post';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'createdBy' => (new UserResource($this->whenLoaded('createdBy')))->hide(['address']),
            'updatedBy' => (new UserResource($this->whenLoaded('updatedBy')))->hide(['address']),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
