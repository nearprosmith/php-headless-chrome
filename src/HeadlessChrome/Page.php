<?php

declare(strict_types=1);

namespace HeadlessChrome;

use WebSocket\Client;

class Page extends Endpoint
{
    protected $wsClient;

    protected $frameId;

    protected $counter = 1;

    public function __construct($description, $devtoolsFrontendUrl, $id, $title, $type, $url, $webSocketDebuggerUrl)
    {
        parent::__construct($description, $devtoolsFrontendUrl, $id, $title, $type, $url, $webSocketDebuggerUrl);
        $this->wsClient = new Client($webSocketDebuggerUrl);
        $this->__send(1, 'Page.enable');
    }

    private function __send($id, $method, $params = []): void
    {
        $this->wsClient->send(json_encode([
            'id' => $id,
            'method' => $method,
            'params' => $params,
        ]));
    }

    private function __waitFor(callable $forResponseFunc, callable $otherFunc = null): void
    {
        while ($data = json_decode($this->wsClient->receive())) {
            if (isset($data->id)) {
                if ($forResponseFunc($data->id, $data) === true) {
                    return;
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
        $this->__send(10, 'Page.navigate', ['url' => $url]);
        $this->__waitFor(
            function ($id, $data): void {
                if ($id === 10) {
                    $this->frameId = $data->result->frameId;
                }
            },
            function ($data) {
                if (isset($data->method) && $data->method === 'Page.frameStoppedLoading' && $data->params->frameId === $this->frameId) {
                    $this->updateStatus();
                    return true;
                }
            }
        );
        return $this;
    }

    public function captureTo(string $file_path)
    {
        $this->__send(10, 'Page.captureScreenshot');
        $this->__waitFor(function ($id, $data) use ($file_path) {
            if ($id === 10) {
                file_put_contents($file_path, base64_decode($data->result->data));
                return true;
            }
        });
        return $this;
    }

    private function updateStatus(): void
    {
        $this->__send(10, 'Page.getNavigationHistory');
        $this->__waitFor(
            function($id,$data){
                if($id === 10){
                    $this->title = $data->result->entries[$data->result->currentIndex]->title;
                    $this->url = $data->result->entries[$data->result->currentIndex]->url;
                    return true;
                }
            }
        );
    }
}
