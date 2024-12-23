<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'tag'   => $this->tag,
            'type'  => 'image',
            'file'  => url('storage/'.$this->file),
            'thumb' => $this->thumb ? url('storage/'.$this->thumb) : '',
        ];
    }
}
