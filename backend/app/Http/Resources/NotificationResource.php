<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => data_get($this->data, 'type'),
            'title' => data_get($this->data, 'title'),
            'message' => data_get($this->data, 'message'),
            'entity_type' => data_get($this->data, 'entity_type'),
            'entity_id' => data_get($this->data, 'entity_id'),
            'target_screen' => data_get($this->data, 'target_screen'),
            'target_params' => data_get($this->data, 'target_params', []),
            'status' => data_get($this->data, 'status'),
            'created_by' => data_get($this->data, 'created_by'),
            'created_at_display' => data_get($this->data, 'created_at_display'),
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
