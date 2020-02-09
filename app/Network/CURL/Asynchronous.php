<?php


namespace App\Network\CURL;


use App\Network\CURL\Contract\Wrapper;

class Asynchronous implements Wrapper
{
    private $mh;

    private $ch;

    private $content;

    public function __construct($mh, $ch)
    {
        $this->mh = $mh;
        $this->setHandle($ch);
    }

    public function setHandle($ch)
    {
        $this->ch = $ch;
        curl_multi_add_handle($this->mh, $this->ch);
    }

    public function exec()
    {
    }

    public function getContent()
    {
        if (is_null($this->content)) {
            $this->content = curl_multi_getcontent($this->ch);
        }
        return $this->content;
    }

    public function close()
    {
        if (!is_null($this->ch)) {
            curl_multi_remove_handle($this->mh, $this->ch);
            curl_close($this->ch);
        }
        $this->ch = null;
    }
}
