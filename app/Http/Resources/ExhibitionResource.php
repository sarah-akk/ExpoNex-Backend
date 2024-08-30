<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ExhibitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (!request()->user()->role_id)
            return [
                'id' => $this->id,
                'title' => $this->name,
                'location' => $this->location,
                'coordinates' => $this->coordinates,
                'start_at' => Carbon::parse($this->start_at)->format('Y-m-d'),
                'end_at' => Carbon::parse($this->end_at)->format('Y-m-d'),
            ];

        if (request()->user()->role_id === 2)
            return [
                'id' => $this->id,
                'title' => $this->name,
                'location' => $this->location,
                'coordinates' => $this->coordinates,
                'start_at' => Carbon::parse($this->start_at)->format('Y-m-d'),
                'end_at' => Carbon::parse($this->end_at)->format('Y-m-d'),
                'size' => $this->size,
            ];

        return [
            'id' => $this->id,
            'title' => $this->name,
            'location' => $this->location,
            'coordinates' => $this->coordinates,
            'start_at' => Carbon::parse($this->start_at)->format('Y-m-d'),
            'end_at' => Carbon::parse($this->end_at)->format('Y-m-d'),
            'size' => $this->size,
            'status' => $this->status,
        ];
    }
}
