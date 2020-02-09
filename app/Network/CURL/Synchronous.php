<?php


namespace App\Network\CURL;


use App\Network\CURL\Contract\Wrapper;

class Synchronous implements Wrapper
{
    private $ch;

    private $content;

    public function __construct($ch)
    {
        $this->setHandle($ch);
    }

    public function setHandle($ch)
    {
        $this->ch = $ch;
    }

    public function exec()
    {
        $this->content = curl_exec($this->ch);
    }

    public function getContent()
    {
        return $this->content;
    }

    public function close()
    {
        curl_close($this->ch);
    }
}
