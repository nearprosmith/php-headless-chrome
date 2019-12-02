<?php

declare(strict_types=1);

namespace HeadlessChrome;

use HeadlessChrome\DevToolsProtocol\DOM;
use HeadlessChrome\DevToolsProtocol\Input;
use HeadlessChrome\DevToolsProtocol\Page as PrtPage;
use HeadlessChrome\DevToolsProtocol\Runtime;

class Page extends Endpoint
{
    protected $wsClient;

    protected $frameId;

    protected $rootNode;

    protected $counter = 1;

    public function __construct($description, $devtoolsFrontendUrl, $id, $title, $type, $url, $webSocketDebuggerUrl)
    {
        parent::__construct($description, $devtoolsFrontendUrl, $id, $title, $type, $url, $webSocketDebuggerUrl);
        $this->wsClient = new DevToolsProtocolClient($webSocketDebuggerUrl);
        $this->__send(1, PrtPage::enable);
    }

    private function __send($id, $method, $params = []): void
    {
        $this->wsClient->send([
            'id' => $id,
            'method' => $method,
            'params' => $params,
        ]);
    }

    private function __waitFor(?callable $forResponseFunc, ?callable $otherFunc = null): void
    {
        while ($data = json_decode($this->wsClient->receive())) {
            if (isset($data->id)) {
                if (is_callable($forResponseFunc)) {
                    if ($forResponseFunc($data->id, $data) === true) {
                        return;
                    }
                }
            } else {
                if (is_callable($otherFunc)) {
                    if ($otherFunc($data) === true) {
                        return;
                    }
                }
            }
        }
    }

    public function moveTo($url)
    {
        $randId = mt_rand();
        $this->__send($randId, PrtPage::navigate, PrtPage::navigateRequest($url));
        $this->__waitFor(
            function ($id, $data) use ($randId): void {
                if ($id === $randId) {
                    $this->frameId = $data->result->frameId;
                }
            },
            function ($data) {
                if (isset($data->method) && $data->method === PrtPage::frameStoppedLoading &&
                    $data->params->frameId === $this->frameId) {
                    $this->updateStatus();
                    return true;
                }
            }
        );

        $this->frameId = null;
        return $this;
    }

    public function captureTo(string $file_path, string $format = 'png', int $quality = null, array $clip = null)
    {
        $this->__send(10, PrtPage::captureScreenshot, PrtPage::captureScreenshotRequest($format, $quality, $clip));
        $this->__waitFor(function ($id, $data) use ($file_path) {
            if ($id === 10) {
                file_put_contents($file_path, base64_decode($data->result->data));
                return true;
            }
        });
        return $this;
    }

    public function type(string $selector, string $value)
    {
        if ($this->rootNode === null) {
            $this->updateRootNode();
        }
        $counter = 10;
        $this->__send($counter, DOM::querySelector, DOM::querySelectorRequest($this->rootNode->nodeId, $selector));
        $this->__waitFor(
            function ($id, $data) use ($value,$counter) {
                if ($id === $counter) {
                    $this->__send($counter + 1, DOM::focus, DOM::focusRequest($data->result->nodeId));
                } elseif ($id === $counter + 1) {
                    $this->__send($counter + 2, Input::insertText, Input::insertTextRequest($value));
                } elseif ($id === $counter + 2) {
                    return true;
                }
            }
        );
        return $this;
    }

    public function submit(string $formSelector)
    {
        $expression = "document.querySelector('{$formSelector}').submit()";
        $this->__send(10, Runtime::evaluate, Runtime::evaluateRequest($expression));
        $this->__waitFor(
            function ($id, $data) {
                if ($id === 10) {
                    return true;
                }
            }
        );
        return $this;
    }

    public function waitForLoading(): void
    {
        $this->__waitFor(null, function ($data) {
            if (isset($data->method) && $data->method === PrtPage::frameStoppedLoading) {
                return true;
            }
        });
        $this->rootNode = null;
        $this->updateStatus();
    }

    private function updateRootNode(): void
    {
        $this->__send(50, DOM::getDocument, DOM::getDocumentRequest());
        $this->__waitFor(
            function ($id, $data) {
                if ($id === 50) {
                    $this->rootNode = $data->result->root;
                    return true;
                }
            }
        );
    }

    private function updateStatus(): void
    {
        $this->__send(10, PrtPage::getNavigationHistory);
        $this->__waitFor(
            function ($id, $data) {
                if ($id === 10) {
                    $this->title = $data->result->entries[$data->result->currentIndex]->title;
                    $this->url = $data->result->entries[$data->result->currentIndex]->url;
                    return true;
                }
            }
        );
    }
}
