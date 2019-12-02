<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

new LoginTwitter();

/**
 * Class LoginTwitter.
 * @property \WebSocket\Client $client
 */
class LoginTwitter
{
    private $client;

    private $frameId;

    private $rootNodeId;

    private $idNodeId;

    private $pwNodeId;

    public function __construct()
    {
//        go(function () {
//            $cmd = '/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --disable-gpu --headless --remote-debugging-port=9222';
//            co::exec($cmd);
//        });

        do {
            $endpoints = json_decode(`curl -s http://localhost:9222/json`);
        } while (empty($endpoints));
        $endpoint = $endpoints[0]->webSocketDebuggerUrl;
        echo '===================' . PHP_EOL;
        echo 'endpoint is found.' . PHP_EOL;
        echo '===================' . PHP_EOL;

        $this->client = new \WebSocket\Client($endpoint);
//        $this->client->send(json_encode([
//            'id' => 1,
//            "method" => 'Page.enable',
//        ]));
//        $this->client->send(json_encode([
//            'id' => 2,
//            "method" => 'Page.navigate',
//            "params" => ['url' => 'https://twitter.com/login']
//        ]));
        $this->client->send(json_encode([
            'id' => 3,
            'method' => 'DOM.getDocument',
        ]));

        $this->channel = new \Swoole\Coroutine\Channel(1024 * 8);

        try {
            $frameId = null;
            while ($data = json_decode($this->client->receive())) {
                go(function () use ($data): void {
                    //sendしたコマンドに対するレスポンスの処理はここ
                    if (isset($data->id)) {
                        switch ($data->id) {
                            case 3:
                                $this->rootNodeId = $data->result->root->nodeId;
                                $this->client->send(json_encode(['id' => 4, 'method' => 'DOM.querySelector', 'params' => ['nodeId' => $this->rootNodeId, 'selector' => "form.signin input[name='session[username_or_email]']"]]));
                                $this->client->send(json_encode(['id' => 14, 'method' => 'DOM.querySelector', 'params' => ['nodeId' => $this->rootNodeId, 'selector' => "form.signin input[name='session[password]']"]]));
                                return;
                            case 4:
                                $this->idNodeId = $data->result->nodeId;
                                while (true) {
                                    print_r($this->channel->stats());
                                    co::sleep(1);
                                }
                                swoole_event_wait();
                                //                                $this->client->send(json_encode(['id' => 5, 'method' => 'DOM.focus', 'params' => ['nodeId' => $data->result->nodeId]]));
                                return;
                            case 14:
                                echo 'Hi';
                                $this->pwNodeId = $data->result->nodeId;
                                $this->channel->push($this->pwNodeId);
                                print_r($this);

                                return;
                            case 5:
                                $this->client->send(json_encode(['id' => 6, 'method' => 'Input.insertText', 'params' => ['text' => 'sarumonera']]));
                                return;
                            case 6:
                                print_r($data);
                                return;
                        }
                    }
                    //受け取るイベントはここ
                    if (isset($data->method)) {
//                        if ($data->method === 'Page.frameStoppedLoading' && $data->params->frameId === $this->frameId) {
//                            $this->client->send(json_encode([
//                                'id' => 3,
//                                "method" => 'DOM.getDocument',
//                            ]));
//                        }
                    }
                });
            }
        } catch (\WebSocket\ConnectionException $e) {
            echo $e->getMessage();
        }

//        exec('pkill -f 9222');
    }
}
