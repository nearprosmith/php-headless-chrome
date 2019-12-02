<?php

declare(strict_types=1);

namespace HeadlessChrome\DevToolsProtocol;

class Network extends DevToolsProtocol
{
    const enable = 'Network.enable';

    const loadingFinished = 'Network.loadingFinished';
}
