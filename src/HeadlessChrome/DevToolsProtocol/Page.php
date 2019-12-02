<?php


namespace HeadlessChrome\DevToolsProtocol;


class Page extends DevToolsProtocol
{
    const navigate = 'Page.navigate';
    const enable = 'Page.enable';
    const captureScreenshot = 'Page.captureScreenshot';
    const frameStoppedLoading = 'Page.frameStoppedLoading';

    static public function navigateRequest($url, $referer = null, $transitionType = null, $frameId = null)
    {
        return DevToolsProtocol::buildRequest(compact('url', 'referer', 'transitionType', 'frameId'));
    }

    static public function captureScreenshotRequest($format = null, $quality = null, $clip = null, $fromSurface = null)
    {
        return DevToolsProtocol::buildRequest(compact('format', 'quality', 'clip', 'fromSurface'));
    }


}