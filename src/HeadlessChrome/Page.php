<?php

declare(strict_types=1);

namespace HeadlessChrome;

use HeadlessChrome\DevToolsProtocol\DOM;
use HeadlessChrome\DevToolsProtocol\Input;
use HeadlessChrome\DevToolsProtocol\Network;
use HeadlessChrome\DevToolsProtocol\Page as PrtPage;

class Page extends Endpoint
{
    protected $wsClient;

    protected $frameId;

    protected $rootNode;

    public function __construct($description, $devtoolsFrontendUrl, $id, $title, $type, $url, $webSocketDebuggerUrl)
    {
        parent::__construct($description, $devtoolsFrontendUrl, $id, $title, $type, $url, $webSocketDebuggerUrl);
        $this->wsClient = new DevToolsProtocolClient($webSocketDebuggerUrl);
        $this->__send(1, PrtPage::enable);
        $this->__send(2,Network::enable);
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
                if(is_callable($forResponseFunc)){
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
        $this->__send(10, PrtPage::navigate, PrtPage::navigateRequest($url));

        $this->__waitFor(
            function ($id, $data): void {
                if ($id === 10) {
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
        $this->__send(10, DOM::querySelector, DOM::querySelectorRequest($this->rootNode->nodeId, $selector));
        $this->__waitFor(
            function ($id, $data) use ($value) {
                if ($id === 10) {
                    $this->__send(11, DOM::focus, DOM::focusRequest($data->result->nodeId));
                } elseif ($id === 11) {
                    $this->__send(12, Input::insertText, Input::insertTextRequest($value));
                } elseif ($id === 12) {
                    return true;
                }
            }
        );
        return $this;
    }

    public function pressEnterOn(string $selector){
        return $this->pressReturnOn($selector);
    }

    public function pressReturnOn(string $selector)
    {
        if ($this->rootNode === null) {
            $this->updateRootNode();
        }
        $this->__send(10, DOM::querySelector, DOM::querySelectorRequest($this->rootNode->nodeId, $selector));
        $this->__waitFor(
            function ($id, $data) {
                if ($id === 10) {
                    $this->__send(11, DOM::focus, DOM::focusRequest($data->result->nodeId));
                } elseif ($id === 11) {
                    return true;
//                    $this->__send(12, Input::dispatchKeyEvent, Input::dispatchKeyEventRequest('keyDown',0,null,null,null,null,'Enter','Enter',13,13,false,false,false));
//                    $this->__send(13, Input::dispatchKeyEvent, Input::dispatchKeyEventRequest('char',0,null,'','',null,'Enter','Enter',13,13,false,false,false));
//                    $this->__send(14, Input::dispatchKeyEvent, Input::dispatchKeyEventRequest('keyUp',0,null,null,null,null,'Enter','Enter',13,13,false,false,false));
                } elseif ($id === 14) {
                    return true;
                }
            }
        );
        return $this;
    }

    public function waitForLoading(): void
    {
        $this->__waitFor(null,function($data){
            if (isset($data->method) && $data->method === Network::loadingFinished) {
                print_r($data);
                return true;
            }
        });
    }

    private function updateRootNode(): void
    {
        $this->__send(10, DOM::getDocument, DOM::getDocumentRequest(0));
        $this->__waitFor(
            function ($id, $data) {
                if ($id === 10) {
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
