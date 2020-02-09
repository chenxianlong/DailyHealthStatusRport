<?php


namespace App\Network;


class CURLMulti
{
    private $handleMap;

    private $mh;

    public function __construct(array $handleMap = [])
    {
        $this->handleMap = $handleMap;
    }

    public function set($key, $handle)
    {
        if (is_null($handle)) {
            unset($this->handleMap[$key]);
        } else {
            $this->handleMap[$key] = $handle;
        }
        return $this;
    }

    public function exec()
    {
        $this->mh = curl_multi_init();

        foreach ($this->handleMap as $handle) {
            curl_multi_add_handle($this->mh, $handle);
        }

        do {
            $status = curl_multi_exec($this->mh, $active);
            if ($active) {
                curl_multi_select($this->mh);
            }
        } while ($active && $status == CURLM_OK);
    }

    public function getContent($key)
    {
        return curl_multi_getcontent($this->handleMap[$key]);
    }

    public function close($closeCurlHandle = false)
    {
        if ($this->mh) {
            foreach ($this->handleMap as $handle) {
                @curl_multi_remove_handle($this->mh, $handle);
            }
            if ($closeCurlHandle) {
                foreach ($this->handleMap as $handle) {
                    curl_close($handle);
                }
            }
            curl_multi_close($this->mh);
            $this->handleMap = [];
            $this->mh = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
