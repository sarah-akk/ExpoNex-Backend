<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (request()->user()->role_id == 2)
            return [
                'id' => $this->id,
                'title' => $this->name,
                'price' => $this->price,
                'quantity' => $this->quantity,
                'pictures' => $this->pictures->map(function ($picture) {
                    return url($picture->path);
                })->toArray(),
            ];
        return [
            'id' => $this->id,
            'title' => $this->name,
            'price' => $this->price,
            'pictures' => $this->pictures->map(function ($picture) {
                return url($picture->path);
            })->toArray(),
            'company' => [
                'id' => $this->company->id,
                'name' => $this->company->name,
            ]
        ];
    }
}
