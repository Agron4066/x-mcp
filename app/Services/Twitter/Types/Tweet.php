<?php

namespace App\Services\Twitter\Types;

class Tweet
{
    public function __construct(
        public readonly string $id,
        public readonly string $text,
        public readonly string $createdAt,
        public readonly array $metrics = [],
        public readonly ?UserInfo $author = null,
        public readonly array $media = [],
        public readonly array $urls = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'created_at' => $this->createdAt,
            'metrics' => $this->metrics,
            'author' => $this->author?->toArray(),
            'media' => $this->media,
            'urls' => $this->urls,
        ];
    }
}
