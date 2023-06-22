<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'restaurant';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = !is_null($this->users) && $this->users->count() > 0 ? $this->users[0] : null;
        return [
            'uuid' => $this->uuid,
            'provider' => $this->provider,
            'name' => $this->name,
            'address' => $this->address,
            'rating' => $this->rating,
            'types' => $this->types,
            'url' => $this->url,
            'open_closed_time' => $this->open_closed_time,
            'is_owning' => $this->when(
                !is_null($this->users),
                !(is_null($user) || is_null($user->pivot->approved_at))
            ),
            'is_subscribing' => $this->when(
                !is_null($this->users),
                !(is_null($user) || is_null($user->pivot->created_at))
            ),
            'subscriber_count' => $this->whenCounted('users'),
            'images' => $this->images,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
