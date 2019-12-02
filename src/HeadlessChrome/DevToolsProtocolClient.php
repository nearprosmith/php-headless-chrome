<?php

declare(strict_types=1);

namespace HeadlessChrome;

use WebSocket\Client;

class DevToolsProtocolClient extends Client
{
    public function send($payload, $opcode = 'text', $masked = true): void
    {
        parent::send(json_encode($payload), $opcode, $masked);
    }
}
