<?php


namespace HeadlessChrome\DevToolsProtocol;


class DevToolsProtocol
{
    static final public function buildRequest(array $args){
        $request = [];
        foreach($args as $key => $value){
            if($value !== null){
                $request[$key] = $value;
            }
        }
        return $request;
    }
}