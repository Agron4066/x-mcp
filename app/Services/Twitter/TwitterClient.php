<?php

namespace App\Services\Twitter;

use App\Services\Twitter\Types\TweetPage;
use App\Services\Twitter\Types\UserInfo;

interface TwitterClient
{
    public function getUserInfo(string $username): UserInfo;

    public function getUserTweets(string $username, array $options = []): TweetPage;

    public function searchTweets(string $query, array $options = []): TweetPage;
}
