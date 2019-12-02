<?php

declare(strict_types=1);

namespace HeadlessChrome\DevToolsProtocol;

class DevToolsProtocol
{
    final public static function buildRequest(array $args)
    {
        $request = [];

        foreach ($args as $key => $value) {
            if ($value !== null) {
                $request[$key] = $value;
            }
        }
        return $request;
    }
}
