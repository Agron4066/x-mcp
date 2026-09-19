<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\ResponseFactory;

#[Description('MCPサーバーの接続確認用。引数なしで呼び出すと、サーバーの稼働状況を返す。')]
#[IsReadOnly]
class PingTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        return Response::structured([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'dataSource' => config('services.twitter.data_source'),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
