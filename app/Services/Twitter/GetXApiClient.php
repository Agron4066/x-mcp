<?php

namespace App\Services\Twitter;

use App\Services\Twitter\Types\Tweet;
use App\Services\Twitter\Types\TweetPage;
use App\Services\Twitter\Types\UserInfo;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use App\Exceptions\TwitterApiException;

class GetXApiClient implements TwitterClient
{
    private const BASE_URL = 'https://api.getxapi.com/twitter';
    private const PER_PAGE = 20;
    private const RETRY_TIMES = 3;
    private const RETRY_SLEEP_MS = 1000;
    private const PAGINATION_INTERVAL_US = 100000; // 100ms

    public function __construct(
        private readonly ?string $apiKey,
    ) {
        if (empty($this->apiKey)) {
            throw new RuntimeException('GETXAPI_KEY is not set. Please set it in .env');
        }
    }

    public function getUserInfo(string $username): UserInfo
    {
        $response = $this->request('/user/info', ['userName' => $username]);

        $user = $response['data'] ?? [];

        return new UserInfo(
            username: $user['userName'] ?? $username,
            name: $user['name'] ?? '',
            followersCount: (int) ($user['followers'] ?? 0),
            description: $user['description'] ?? null,
            profileImageUrl: $user['profilePicture'] ?? null,
        );
    }

    public function getUserTweets(string $username, array $options = []): TweetPage
    {
        $count = min($options['count'] ?? self::PER_PAGE, 200);
        $includeReplies = $options['includeReplies'] ?? false;

        $endpoint = $includeReplies ? '/user/tweets_and_replies' : '/user/tweets';

        return $this->paginatedFetch($endpoint, ['userName' => $username], $count);
    }

    public function searchTweets(string $query, array $options = []): TweetPage
    {
        if (empty(trim($query))) {
            throw new RuntimeException('Search query cannot be empty');
        }

        $count = min($options['count'] ?? self::PER_PAGE, 200);
        $product = $options['product'] ?? 'Latest';

        return $this->paginatedFetch('/tweet/advanced_search', [
            'q' => $query,
            'product' => $product,
        ], $count);
    }

    private function paginatedFetch(string $endpoint, array $params, int $targetCount): TweetPage
    {
        $allTweets = [];
        $apiCalls = 0;
        $cursor = null;

        while (count($allTweets) < $targetCount) {
            $queryParams = $params;
            if ($cursor !== null) {
                $queryParams['cursor'] = $cursor;
            }

            $response = $this->request($endpoint, $queryParams);
            $apiCalls++;

            $tweets = $this->extractTweets($response);
            $allTweets = array_merge($allTweets, $tweets);

            $cursor = $response['next_cursor'] ?? null;
            $hasMore = $response['has_more'] ?? false;

            if (!$hasMore || $cursor === null) {
                break;
            }

            usleep(self::PAGINATION_INTERVAL_US);
        }

        $allTweets = array_slice($allTweets, 0, $targetCount);

        return new TweetPage(
            tweets: $allTweets,
            nextCursor: $cursor,
            hasMore: $hasMore ?? false,
            apiCallsUsed: $apiCalls,
        );
    }

    private function extractTweets(array $response): array
    {
        $tweetsData = $response['data']['tweets'] ?? $response['tweets'] ?? [];

        return array_map(function (array $t) {
            $author = null;
            if (isset($t['author'])) {
                $author = new UserInfo(
                    username: $t['author']['userName'] ?? '',
                    name: $t['author']['name'] ?? '',
                    followersCount: (int) ($t['author']['followers'] ?? 0),
                );
            }

            return new Tweet(
                id: $t['id'] ?? '',
                text: $t['text'] ?? '',
                createdAt: $t['createdAt'] ?? '',
                metrics: [
                    'likes' => (int) ($t['likeCount'] ?? 0),
                    'retweets' => (int) ($t['retweetCount'] ?? 0),
                    'replies' => (int) ($t['replyCount'] ?? 0),
                    'views' => (int) ($t['viewCount'] ?? 0),
                ],
                author: $author,
                media: $t['media'] ?? [],
                urls: $t['entities'] ?? [],
            );
        }, $tweetsData);
    }

    private function request(string $endpoint, array $params = []): array
    {
        $response = Http::withToken($this->apiKey)
            ->retry(self::RETRY_TIMES, self::RETRY_SLEEP_MS, function ($exception) {
                return $exception->getCode() === 429;
            })
            ->get(self::BASE_URL . $endpoint, $params);

        if ($response->failed()) {
            throw new TwitterApiException(
                "GetXAPI request failed: {$response->status()}",
                $response->status(),
                $response->body()
            );
        }

        return $response->json() ?? [];
    }
}
