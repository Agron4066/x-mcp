<?php

namespace App\Services\Twitter;

use App\Services\Twitter\Types\TweetPage;
use App\Services\Twitter\Types\UserInfo;

class GetXApiClient implements TwitterClient
{
    public function __construct(
        private readonly ?string $apiKey,
    ) {}

    public function getUserInfo(string $username): UserInfo
    {
        throw new \RuntimeException('Not implemented yet');
    }

    public function getUserTweets(string $username, array $options = []): TweetPage
    {
        throw new \RuntimeException('Not implemented yet');
    }

    public function searchTweets(string $query, array $options = []): TweetPage
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
