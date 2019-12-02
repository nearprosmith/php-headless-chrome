<?php

declare(strict_types=1);

namespace HeadlessChrome\DevToolsProtocol;

class DOM extends DevToolsProtocol
{
    const querySelector = 'DOM.querySelector';

    const getDocument = 'DOM.getDocument';

    const focus = 'DOM.focus';

    public static function querySelectorRequest(int $nodeId, string $selector)
    {
        return compact('nodeId', 'selector');
    }

    public static function getDocumentRequest(int $depth = null, bool $piere = null)
    {
        return DevToolsProtocol::buildRequest(compact('depth', 'piere'));
    }

    public static function focusRequest(int $nodeId = null, int $backendNodeId = null, string $objectId = null)
    {
        return DevToolsProtocol::buildRequest(compact('nodeId', 'backendNodeId', 'objectId'));
    }
}
