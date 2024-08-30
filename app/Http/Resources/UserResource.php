<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (!request()->user()) {
            if (!$this->role_id || $this->role_id == 3)
                return [
                    'name' => $this->name,
                    'email' => $this->email,
                    'username' => $this->username,
                    'channel_id' => $this->channel_id,
                    'phone_number' => $this->phone_number,
                    'profile_picture' => $this->profile_picture?->path ? url($this->profile_picture?->path) : null,
                ];
            if ($this->role->name == 'company_owner')
                return [
                    'name' => $this->name,
                    'email' => $this->email,
                    'username' => $this->username,
                    'channel_id' => $this->channel_id,
                    'phone_number' => $this->phone_number,
                    'profile_picture' => $this->profile_picture?->path ? url($this->profile_picture?->path) : null,
                    'company' => CompanyDetailsResource::make($this->company)->additional([
                        'show_owner' => $this->company->show_owner
                    ])
                ];
            return [
                'name' => $this->name,
                'email' => $this->email,
                'username' => $this->username,
                'channel_id' => $this->channel_id,
                'phone_number' => $this->phone_number,
                'can' => $this->permissions
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'is_verified' => $this->is_verified,
            'is_pending' => $this->is_pending,
            'profile_picture' => $this->profile_picture?->path ? url($this->profile_picture?->path) : null,
            'has_company' => $this->company ? [
                'id' => $this->company->id,
                'name' => $this->company->name
            ] : null,
        ];
    }
}
