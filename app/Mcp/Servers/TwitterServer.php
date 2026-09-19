<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Twitter Server')]
#[Version('0.0.1')]
#[Instructions('Instructions describing how to use the server and its features.')]
class TwitterServer extends Server
{
    protected array $tools = [
        \App\Mcp\Tools\PingTool::class,
        \App\Mcp\Tools\FetchAccountTweetsTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
