<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'barcode' => $this->barcode,
            'side_style' => [
                'type' => $this->side_style ? 'color' : 'picture',
                'style' => $this->side_style ?? url($this->side_picture->path),
            ],
            'main_style' => [
                'type' => $this->main_style ? 'color' : 'picture',
                'style' => $this->main_style ?? url($this->main_picture->path),
            ]
        ];
    }
}
