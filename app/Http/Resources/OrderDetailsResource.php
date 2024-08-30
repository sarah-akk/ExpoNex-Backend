<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class OrderDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (!request()->user()->role_id)
            return [
                'order_number' => $this->order_number,
                'amount' => $this->amount,
                'status' => $this->status,
                'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
                $this->mergeWhen(
                    count($this->items) !== 0 || $this->ticketItems,
                    [
                        'items' => [
                            'type' => count($this->items) !== 0 ? 'products' : 'tickets',
                            'data' => count($this->items) !== 0 ?
                                CartItemsResource::collection($this->items) :
                                TicketItemsResource::collection($this->ticketItems)
                        ]
                    ],
                ),
            ];

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'amount' => $this->amount,
            'status' => $this->status,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            $this->mergeWhen(
                count($this->items) !== 0 && $this->ticketItems,
                [
                    'items' => [
                        'type' => count($this->items) !== 0 ? 'products' : 'tickets',
                        'data' => count($this->items) !== 0 ?
                            CartItemsResource::collection($this->items) :
                            TicketItemsResource::make($this->ticketItems)
                    ]
                ],
            ),
        ];
    }
}
