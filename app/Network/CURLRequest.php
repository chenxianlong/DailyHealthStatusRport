<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-1
 * Time: 下午11:10
 */

namespace App\Network;


use App\Network\Contract\Coroutinable;
use App\Network\Contract\Request as RequestContract;
use App\Network\Exception\RequestException;
use App\Network\Utils\CURLCommon;

class CURLRequest implements RequestContract, Coroutinable
{
    private $ch;

    private $headers = [];

    private $headerLists = [];

    private $coroutineMode = false;

    /**
     * CURLAPIRequest constructor.
     * @param resource $ch cURL handle
     * @throws RequestException
     */
    public function __construct($ch)
    {
        if (is_resource($ch)) {
            $this->ch = $ch;
        } else if (is_string($ch)) {
            $this->ch = curl_init($ch);
        } else {
            throw new RequestException("Invalid constructor parameter");
        }
    }

    public function setCoroutineMode(bool $mode)
    {
        $this->coroutineMode = $mode;
        return $this;
    }

    public function withPostFields($postFields, $asJSON = false)
    {
        $additionalHeaders = CURLCommon::setPostfieldsIfNeed($this->ch, $postFields, $asJSON);
        $this->headerLists[] = $additionalHeaders;
        return $this;
    }

    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setHeaderLists(... $headerLists)
    {
        $this->headerLists = $headerLists;
        return $this;
    }

    public function response(&$error = null, &$errno = null)
    {
        if ($this->coroutineMode) {
            return $this->doResponse(... func_get_args());
        } else {
            return $this->doResponse(... func_get_args())->getReturn();
        }
    }

    /**
     * @inheritdoc
     */
    private function doResponse(&$error = null, &$errno = null)
    {
        try {
            CURLCommon::setHTTPHeaders($this->ch, $this->headers, ... $this->headerLists);

            if ($this->coroutineMode) {
                $response = yield $this->ch;
            } else {
                $response = curl_exec($this->ch);
            }

            if ($response === false) {
                $error = curl_error($this->ch);
                $errno = curl_errno($this->ch);
            }
            return $response;
        } finally {
            $this->closeHandle();
        }
    }

    public function responseOrFail()
    {
        if ($this->coroutineMode) {
            return $this->doResponseOrFail();
        } else {
            return $this->doResponseOrFail()->getReturn();
        }
    }

    /**
     * @inheritdoc
     */
    private function doResponseOrFail()
    {
        try {
            CURLCommon::setHTTPHeaders($this->ch, $this->headers, ... $this->headerLists);

            if ($this->coroutineMode) {
                $response = (yield $this->ch);
            } else {
                $response = curl_exec($this->ch);
            }

            if ($response === false)
                throw new RequestException(curl_error($this->ch), RequestException::ERRNO_NETWORK_ERROR, $response);
            return $response;
        } finally {
            // $this->closeHandle();
        }
    }

    public function JSONResponse($assoc = false, &$rawResponse = null)
    {
        if ($this->coroutineMode) {
            return $this->doJSONResponse(... func_get_args());
        } else {
            return $this->doJSONResponse(... func_get_args())->getReturn();
        }
    }

    /**
     * @inheritdoc
     */
    private function doJSONResponse($assoc = false, &$rawResponse = null)
    {
        $this->addHeader("Accept", "application/json,*/*");

        if ($this->coroutineMode) {
            $rawResponse = yield from $this->responseOrFail();
        } else {
            $rawResponse = $this->responseOrFail();
        }
        $decodedData = json_decode($rawResponse, $assoc);
        if (is_null($decodedData))
            throw new RequestException(json_last_error_msg(), RequestException::ERROR_DATA_PARSE_ERROR, $rawResponse);
        return $decodedData;
    }

    public function __destruct()
    {
        $this->closeHandle();
    }

    private function closeHandle()
    {
        if (!is_null($this->ch)) {
            @curl_close($this->ch);
            $this->ch = null;
        }
    }
}
