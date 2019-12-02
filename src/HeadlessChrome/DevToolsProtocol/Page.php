<?php

declare(strict_types=1);

namespace HeadlessChrome\DevToolsProtocol;

class Page extends DevToolsProtocol
{
    const CAPTURE_TYPE_PNG = 'png';

    const CAPTURE_TYPE_JPEG = 'jpeg';

    const navigate = 'Page.navigate';

    const enable = 'Page.enable';

    const getNavigationHistory = 'Page.getNavigationHistory';

    const captureScreenshot = 'Page.captureScreenshot';

    const frameStoppedLoading = 'Page.frameStoppedLoading';

    public static function navigateRequest($url, $referer = null, $transitionType = null, $frameId = null)
    {
        return DevToolsProtocol::buildRequest(compact('url', 'referer', 'transitionType', 'frameId'));
    }

    public static function captureScreenshotRequest($format = null, $quality = null, $clip = null, $fromSurface = null)
    {
        return DevToolsProtocol::buildRequest(compact('format', 'quality', 'clip', 'fromSurface'));
    }
}
