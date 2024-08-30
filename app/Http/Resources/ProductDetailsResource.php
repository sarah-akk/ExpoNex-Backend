<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (request()->user()->role_id == 2)
            return [
                'id' => $this->id,
                'title' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'quantity' => $this->quantity,
                'pictures' => $this->pictures->map(function ($picture) {
                    return url($picture->path);
                })->toArray(),
                'items_solde' => array_sum($this->cart->map(function ($item) {
                    return $item->quantity;
                })->toArray()),
                'categories' => CategoryResource::collection($this->categories),
            ];

        return [
            'id' => $this->id,
            'title' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'pictures' => $this->pictures->map(function ($picture) {
                return url($picture->path);
            })->toArray(),
            'company' => [
                'id' => $this->company->id,
                'name' => $this->company->name,
            ],
            'categories' => CategoryResource::collection($this->categories),
        ];
    }
}
