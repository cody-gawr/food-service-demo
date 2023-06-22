<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'restaurant_uuid' => $this->whenPivotLoaded('user_has_roles', function () {
                return $this->pivot->restaurant_uuid;
            }),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions'))
        ];
    }
}
