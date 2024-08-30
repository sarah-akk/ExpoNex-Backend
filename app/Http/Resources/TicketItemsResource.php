<?php

namespace App\Http\Resources;

use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketItemsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'exhibition' => [
                'id' => $this->ticket->exhibition_id,
                'name' => Exhibition::find( $this->ticket->exhibition_id)->name,
            ],
        ];
    }
}