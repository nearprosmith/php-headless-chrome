<?php

declare(strict_types=1);

namespace HeadlessChrome;

class Chrome
{
    private $pid;

    private $port;

    private $endpoints = [];

    private $pages = [];

    /**
     * Chrome constructor.
     * @param null|string $path_to_chrome If headless chrome has been already launched by otherways, set to null
     * @param int         $port
     */
    public function __construct(string $path_to_chrome = null, int $port = 9222)
    {
        $this->port = $port;
        // Chromeのパスが指定されていなかったらすでに起動中
        if ($path_to_chrome !== null) {
            $cmd = "{$path_to_chrome} --disable-gpu --headless --remote-debugging-port={$port}";
            $output = [];
            exec($cmd . ' > /dev/null & echo $!', $output);
            $this->pid = $output[0] ?? null;
        } else {
            $this->pid = null;
        }

        $this->updateEndpoints();
    }

    public function __destruct()
    {
        if ($this->pid !== null) {
            exec('kill -9 ' . $this->pid);
        }
    }

    /**
     * @param int $position
     * @return null|mixed
     */
    public function getPage(int $position): Page
    {
        if (isset($this->pages[$position])) {
            return $this->pages[$position];
        }
    }

    public function updateEndpoints(): void
    {
        $start = microtime(true);

        do {
            $curlRes = `curl -s http://localhost:{$this->port}/json`;

            if ($curlRes !== null) {
                $endpoints = json_decode($curlRes);
            }

            if (microtime(true) - $start > 3) {
                throw new \Exception('Failed to fetch endpoint json: timeout. Are');
            }
        } while (empty($endpoints));

        foreach ($endpoints as $eachEndPoint) {
            if ($eachEndPoint->type === Endpoint::TYPE_PAGE) {
                $page = new Page(
                    $eachEndPoint->description,
                    $eachEndPoint->devtoolsFrontendUrl,
                    $eachEndPoint->id,
                    $eachEndPoint->title,
                    $eachEndPoint->type,
                    $eachEndPoint->url,
                    $eachEndPoint->webSocketDebuggerUrl
                );
                $this->endpoints[] = $page;
                $this->pages[] = $page;
            } else {
                $this->endpoints[] = new Endpoint(
                    $eachEndPoint->description,
                    $eachEndPoint->devtoolsFrontendUrl,
                    $eachEndPoint->id,
                    $eachEndPoint->title,
                    $eachEndPoint->type,
                    $eachEndPoint->url,
                    $eachEndPoint->webSocketDebuggerUrl
                );
            }
        }
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        if ($this->pid !== null) {
            return true;
        }

        if (!empty($this->endpoints)) {
            return true;
        }
        return false;
    }

    /**
     * @return array|mixed
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }

    public function getPid()
    {
        return $this->pid;
    }
}
