<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->getId(),
            'content' => $this->getContent(),
            'file_path' => $this->getFilePath(),
            'file_name' => $this->getFileName(),
            'file_type' => $this->getFileType(),
            'created_at' => $this->getFormattedCreatedAt(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->getId(),
                'name' => $this->user->getName(),
                'email' => $this->user->getEmail(),
            ]),
        ];
    }
}
