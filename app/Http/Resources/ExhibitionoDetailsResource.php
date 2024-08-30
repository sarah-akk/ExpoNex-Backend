<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ExhibitionoDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        //TODO add more data
        if (!request()->user()->role_id)
            return [
                'id' => $this->id,
                'title' => $this->name,
                'description' => $this->description,
                'location' => $this->location,
                'coordinates' => $this->coordinates,
                'start_at' => Carbon::parse($this->start_at)->format('Y-m-d'),
                'end_at' => Carbon::parse($this->end_at)->format('Y-m-d'),
//                'profile_picture' => url($this->profile_picture->path),
                'ticket_in_place' => $this->ticketManager->in_place,
                'ticket_in_place_price' => $this->ticketManager->in_place_price,
                'ticket_in_virtual_price' => $this->ticketManager->in_virtual_price,
                'ticket_prime' => $this->ticketManager->prime,
                'ticket_prime_price' => $this->ticketManager->prime_price,
            ];

        if (request()->user()->role_id === 2)
            return [
                'id' => $this->id,
                'title' => $this->name,
                'description' => $this->description,
                'location' => $this->location,
                'coordinates' => $this->coordinates,
                'start_at' => Carbon::parse($this->start_at)->format('Y-m-d'),
                'end_at' => Carbon::parse($this->end_at)->format('Y-m-d'),
//                'profile_picture' => url($this->profile_picture->path),
                'size' => $this->size,
                'ticket_in_place' => $this->ticketManager->in_place,
                'ticket_in_place_price' => $this->ticketManager->in_place_price,
                'ticket_in_virtual_price' => $this->ticketManager->in_virtual_price,
                'ticket_prime' => $this->ticketManager->prime,
                'ticket_prime_price' => $this->ticketManager->prime_price,
                'width' => $this->mapManager->width,
                'height' => $this->mapManager->height,
                'block_size' => $this->mapManager->block_size,
                'sections' => $this->mapManager->sections()->where('company_id', null)->get(),
            ];
        if (request()->user()->role_id === 3)
            return [
                'id' => $this->id,
                'title' => $this->name,
                'description' => $this->description,
                'location' => $this->location,
                'coordinates' => $this->coordinates,
                'start_at' => Carbon::parse($this->start_at)->format('Y-m-d'),
                'end_at' => Carbon::parse($this->end_at)->format('Y-m-d'),
//                'profile_picture' => url($this->profile_picture->path),
                'size' => $this->size,
                'ticket_in_place' => $this->ticketManager->in_place,
                'ticket_in_place_price' => $this->ticketManager->in_place_price,
                'ticket_in_virtual_price' => $this->ticketManager->in_virtual_price,
                'ticket_prime' => $this->ticketManager->prime,
                'ticket_prime_price' => $this->ticketManager->prime_price,
                'width' => $this->mapManager->width,
                'height' => $this->mapManager->height,
                'block_size' => $this->mapManager->block_size,
                'sections' => $this->mapManager->sections,
            ];

        return [
            'id' => $this->id,
            'title' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'coordinates' => $this->coordinates,
            'start_at' => Carbon::parse($this->start_at)->format('Y-m-d'),
            'end_at' => Carbon::parse($this->end_at)->format('Y-m-d'),
//            'profile_picture' => url($this->profile_picture->path),
            'size' => $this->size,
            'status' => $this->status,
            'ticket_in_place' => $this->ticketManager->in_place,
            'ticket_in_place_price' => $this->ticketManager->in_place_price,
            'ticket_in_virtual_price' => $this->ticketManager->in_virtual_price,
            'ticket_prime' => $this->ticketManager->prime,
            'ticket_prime_price' => $this->ticketManager->prime_price,
            'width' => $this->mapManager->width,
            'height' => $this->mapManager->height,
            'block_size' => $this->mapManager->block_size,
            'sections' => $this->mapManager->sections->each(function ($i, $k) {
                $i->makeVisible(['company_id']);
            }),
            'documents' => $this->docs->pluck('id')->toArray(),
            'ticket_design' => TicketResource::make($this->ticketManager),
        ];
    }
}
