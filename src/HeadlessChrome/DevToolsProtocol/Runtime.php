<?php

declare(strict_types=1);

namespace HeadlessChrome\DevToolsProtocol;

class Runtime extends DevToolsProtocol
{
    const enable = 'Runtime.enable';

    const evaluate = 'Runtime.evaluate';

    public static function evaluateRequest(string $expression)
    {
        return DevToolsProtocol::buildRequest(compact('expression'));
    }
}
