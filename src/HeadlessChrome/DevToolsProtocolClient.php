<?php


namespace HeadlessChrome;

use WebSocket\Client;

class DevToolsProtocolClient extends Client
{
    public function send($payload, $opcode = 'text', $masked = true)
    {
        parent::send(json_encode($payload), $opcode, $masked);
    }

    public function waitFor(callable $forResponseFunc, callable $otherFunc = null){
        while($data = json_decode($this->receive())){
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


}