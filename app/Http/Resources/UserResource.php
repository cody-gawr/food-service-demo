<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\HasHiddenFields;
use Illuminate\Http\Request;

class UserResource extends JsonResource
{
    use HasHiddenFields;
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'user';
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->filterFields([
            'uuid' => $this->uuid,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'address' => $this->address,
            'avatar_url' => $this->avatar_url,
            'is_following' => $this->whenHas('is_following'),
            'is_follower' => $this->whenHas('is_follower'),
            'follower_count' => $this->whenCounted('followers'),
            'following_count' => $this->whenCounted('followings'),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'created_at' => $this->updated_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
