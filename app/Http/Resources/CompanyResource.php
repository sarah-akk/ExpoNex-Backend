<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        if (!request()->user()->role_id)
            return [
                'id' => $this->id,
                'name' => $this->name,
                'companyname' => $this->companyname,
                'is_verified' => $this->is_verified,
            ];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'companyname' => $this->companyname,
            'is_verified' => $this->is_verified,
            'is_approval' => $this->is_approval,
            'owner' => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
            ]
        ];
    }
}
