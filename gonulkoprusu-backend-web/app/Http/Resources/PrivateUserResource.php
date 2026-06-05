<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full profile - returned ONLY to the account owner or an admin.
 * Includes the PRIVATE fields. The username is read-only on the client.
 */
class PrivateUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'username'      => $this->username,      // READ-ONLY
            'first_name'    => $this->first_name,    // PRIVATE
            'last_name'     => $this->last_name,     // PRIVATE
            'email'         => $this->email,         // PRIVATE
            'phone'         => $this->phone,         // PRIVATE
            'gender'        => $this->gender,
            'city'          => $this->city,
            'district'      => $this->district,
            'profile_photo' => $this->profile_photo,
            'bio'           => $this->bio,
            'role'          => $this->role,
            'status'        => $this->status,
            'is_premium'    => (bool) $this->is_premium,
            'created_at'    => $this->created_at,
        ];
    }
}
