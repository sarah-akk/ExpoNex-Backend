<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'channel_id' => $this->channel_id,
            'phone_number' => $this->phone_number,
            'profile_picture' => $this->profile_picture?->path ? url($this->profile_picture?->path) : null,
            'is_verified' => $this->is_verified,
            'is_pending' => $this->is_pending,
            'company' => $this->company ? CompanyResource::make($this->company) : null
        ];
    }
}
