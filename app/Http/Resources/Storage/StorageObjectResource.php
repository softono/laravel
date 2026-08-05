<?php

namespace App\Http\Resources\Storage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorageObjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->object_key,
            'size' => $this->size,
            'etag' => $this->etag(),
            'mime_type' => $this->mime_type,
            'last_modified' => $this->updated_at?->toIso8601String(),
        ];
    }
}
