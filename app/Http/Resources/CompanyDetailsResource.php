<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (!request()->user())
            return [
                'id' => $this->id,
                'name' => $this->name,
                'companyname' => $this->companyname,
                'description' => $this->description,
                'is_verified' => $this->is_verified,
//                'profile_picture' => url($this->profile_picture->path),
                'show_owner' => $this->show_owner,
            ];

        if (!request()->user()->role_id)
            return [
                'name' => $this->name,
                'companyname' => $this->companyname,
                'description' => $this->description,
                'is_verified' => $this->is_verified,
//                'profile_picture' => url($this->profile_picture->path),
                $this->mergeWhen($this->show_owner, [
                    'owner' => [
                        'name' => $this->owner->name,
                        'username' => $this->owner->username,
                    ],
                ]),
            ];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'companyname' => $this->companyname,
            'description' => $this->description,
            'is_verified' => $this->is_verified,
//            'profile_picture' => url($this->profile_picture->path),
            'show_owner' => $this->show_owner,
            'is_approval' => $this->is_approval,
            'is_pending' => $this->is_pending,
            'documents_ids' => $this->docs->pluck('id'),
            'owner' => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'username' => $this->owner->username,
            ],
        ];
    }
}
