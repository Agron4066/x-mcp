<?php

namespace App\Services\Twitter\Types;

class TweetPage
{
    /**
     * @param Tweet[] $tweets
     */
    public function __construct(
        public readonly array $tweets,
        public readonly ?string $nextCursor = null,
        public readonly bool $hasMore = false,
        public readonly int $apiCallsUsed = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'tweets' => array_map(fn (Tweet $t) => $t->toArray(), $this->tweets),
            'next_cursor' => $this->nextCursor,
            'has_more' => $this->hasMore,
            'api_calls_used' => $this->apiCallsUsed,
        ];
    }
}
