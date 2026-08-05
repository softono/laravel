<?php

namespace App\Http\Resources\Storage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BucketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'uuid' => $this->uuid,
            'visibility' => $this->visibility,
            'storage_quota' => $this->storage_quota,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
