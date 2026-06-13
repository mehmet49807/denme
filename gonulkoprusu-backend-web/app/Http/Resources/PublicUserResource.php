<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * What OTHER users are allowed to see.
 * PRIVATE fields (real name, email, phone) are NEVER included here.
 */
class PublicUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'username'      => $this->username,
            'profile_photo' => $this->profile_photo,
            'bio'           => $this->bio,
            'gender'        => $this->gender,
            // City - District displayed side-by-side inside a bounded box on clients.
            'city'          => $this->city,
            'district'      => $this->district,
            'is_premium'    => (bool) $this->is_premium,
        ];
    }
}
