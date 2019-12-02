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
        $this->wsClient->send(json_encode([
            'id' => 1,
            "method" => 'Page.enable',
        ]));
    }

    public function moveTo($url)
    {
        $this->wsClient->send(json_encode([
            'id' => 10,
            "method" => 'Page.navigate',
            "params" => ['url' => $url]
        ]));
        try {
            while ($data = json_decode($this->wsClient->receive())) {
                if (isset($data->id) && $data->id === 10) {
                    $this->frameId = $data->result->frameId;
                }
                if (isset($data->method) && $data->method == 'Page.frameStoppedLoading' && $data->params->frameId === $this->frameId) {

                    $this->updateStatus();

                    return $this;
                }
            }
        } catch (\WebSocket\ConnectionException $e) {
        }
    }
    private function updateStatus(): void
    {
        $this->wsClient->send(json_encode([
            'id' => 10,
            'method' => 'Page.getNavigationHistory'
        ]));
        try {
            while ($data = json_decode($this->wsClient->receive())) {
                if (isset($data->id) && $data->id === 10) {
                    $this->title = $data->result->entries[$data->result->currentIndex]->title;
                    $this->url = $data->result->entries[$data->result->currentIndex]->url;
                    return;
                }
            }
        } catch (\WebSocket\ConnectionException $e) {
        }
    }
}
