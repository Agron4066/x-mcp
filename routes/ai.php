<?php

use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/twitter', \App\Mcp\Servers\TwitterServer::class);
