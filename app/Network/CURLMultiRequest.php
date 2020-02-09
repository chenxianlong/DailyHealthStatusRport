<?php


namespace App\Network;


class CURLMultiRequest
{
    private $method;

    private $arguments;

    /**
     * @var CURLRequest[] $requests
     */
    private $requests = [];

    private $curlMulti;

    /**
     * @var \Generator[] $generators
     */
    private $generators;

    public function __construct(string $method, ... $arguments)
    {
        $this->method = $method;
        $this->arguments = $arguments;
    }

    public function set($key, CURLRequest $request)
    {
        if (is_null($key)) {
            unset($this->requests[$key]);
        } else {
            $this->requests[$key] = $request;
        }
        return $this;
    }

    public function exec()
    {
        $this->curlMulti = $curlMulti = new CURLMulti();
        $this->generators = [];
        foreach ($this->requests as $key => $request) {
            $request->setCoroutineMode(true);
            /**
             * @var \Generator $generator
             */
            $this->generators[$key] = $generator = call_user_func([$request, $this->method], ... $this->arguments);
            $curlMulti->set($key, $generator->current());
        }

        $curlMulti->exec();
    }

    public function getContent($key)
    {
        $generator = $this->generators[$key];
        $generator->send($this->curlMulti->getContent($key));
        return $generator->getReturn();
    }

    /**
     * @return CURLRequest[]
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    public function close()
    {
        if ($this->curlMulti) {
            $this->curlMulti->close();
            $this->curlMulti = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
