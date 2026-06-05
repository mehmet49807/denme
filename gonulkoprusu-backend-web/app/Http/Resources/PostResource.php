<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Instagram-like feed item.
 *   Left  : username
 *   Right : city - district (bounded box on clients)
 * Comments are intentionally absent - the feature is disabled platform-wide.
 */
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'image_url'    => $this->image_url,
            'caption'      => $this->caption,
            'likes_count'  => (int) $this->likes_count,
            'liked_by_me'  => $this->isLikedBy($request->user()),
            'comments_enabled' => false, // hard contract: comments are closed
            'author'       => new PublicUserResource($this->whenLoaded('user')),
            'created_at'   => $this->created_at,
        ];
    }
}
