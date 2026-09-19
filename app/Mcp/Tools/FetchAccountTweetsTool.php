<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use App\Services\Twitter\TwitterClient;
use App\Exceptions\TwitterApiException;

#[Description('Fetch tweets from a specific X account with user info and pagination.')]
class FetchAccountTweetsTool extends Tool
{
    public function __construct(
        private TwitterClient $client,
    ) {}

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:1'],
            'count' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'include_replies' => ['sometimes', 'boolean'],
        ]);

        $username = $validated['username'];
        $count = $validated['count'] ?? 20;
        $includeReplies = $validated['include_replies'] ?? false;

        try {
            $userInfo = $this->client->getUserInfo($username);
        } catch (TwitterApiException $e) {
            if ($e->isNotFound) {
                return Response::error("User '@{$username}' not found.");
            }
            return Response::error("Failed to fetch user info: {$e->getMessage()}");
        }

        try {
            $tweetPage = $this->client->getUserTweets($username, [
                'count' => $count,
                'includeReplies' => $includeReplies,
            ]);
        } catch (TwitterApiException $e) {
            return Response::error("Failed to fetch tweets: {$e->getMessage()}");
        }

        $data = [
            'account' => $userInfo->toArray(),
            'tweets' => array_map(fn ($t) => $t->toArray(), $tweetPage->tweets),
            'meta' => [
                'total_fetched' => count($tweetPage->tweets),
                'api_calls_used' => $tweetPage->apiCallsUsed + 1, // +1 はユーザー情報取得分
                'has_more' => $tweetPage->hasMore,
            ],
        ];

        return Response::structured($data);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'username' => $schema->string()
                ->description('取得対象のXアカウント名（@なし）')
                ->required(),
            'count' => $schema->integer()
                ->description('取得件数の上限（デフォルト20、最大200）')
                ->default(20),
            'include_replies' => $schema->boolean()
                ->description('リプライも含めるか（デフォルトfalse）')
                ->default(false),
        ];
    }
}
