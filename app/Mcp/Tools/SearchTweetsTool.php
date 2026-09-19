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

// ←← 機能2で作成 ←←
#[Description('Search tweets across X with advanced query syntax and return structured results.')]
class SearchTweetsTool extends Tool
{
    public function __construct(
        private TwitterClient $client,
    ) {}

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1'],
            'count' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'product' => ['sometimes', 'string', 'in:Latest,Top'],
        ]);

        $query = $validated['query'];
        $count = $validated['count'] ?? 20;
        $product = $validated['product'] ?? 'Latest';

        try {
            $tweetPage = $this->client->searchTweets($query, [
                'count' => $count,
                'product' => $product,
            ]);
        } catch (TwitterApiException $e) {
            return Response::error("Failed to search tweets: {$e->getMessage()}");
        }

        $data = [
            'query' => $query,
            'product' => $product,
            'tweets' => array_map(fn ($t) => $t->toArray(), $tweetPage->tweets),
            'meta' => [
                'total_fetched' => count($tweetPage->tweets),
                'api_calls_used' => $tweetPage->apiCallsUsed,
                'has_more' => $tweetPage->hasMore,
            ],
        ];

        return Response::structured($data);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('検索クエリ。Xの高度な検索構文対応（from: to: since: until: min_faves: #ハッシュタグ "完全一致" OR 等）')
                ->required(),
            'count' => $schema->integer()
                ->description('取得件数の上限（デフォルト20、最大200）')
                ->default(20),
            'product' => $schema->string()
                ->description('ソート順。Latest（新着順）または Top（エンゲージメント順）。デフォルト Latest')
                ->default('Latest'),
        ];
    }
}
// ←← 作成ここまで ←←
