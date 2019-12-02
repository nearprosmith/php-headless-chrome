<?php

declare(strict_types=1);

namespace HeadlessChrome;

class Endpoint
{
    const TYPE_PAGE = 'page';

    protected $description;

    protected $devtoolsFrontendUrl;

    protected $id;

    protected $title;

    protected $type;

    protected $url;

    protected $webSocketDebuggerUrl;

    public function __construct($description, $devtoolsFrontendUrl, $id, $title, $type, $url, $webSocketDebuggerUrl)
    {
        $this->description = $description;
        $this->devtoolsFrontendUrl = $devtoolsFrontendUrl;
        $this->id = $id;
        $this->title = $title;
        $this->type = $type;
        $this->url = $url;
        $this->webSocketDebuggerUrl = $webSocketDebuggerUrl;
    }

    public function __get($name)
    {
        return $this->{$name};
    }
}
