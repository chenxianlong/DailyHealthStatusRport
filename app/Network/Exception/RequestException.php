<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-3
 * Time: 下午1:14
 */

namespace App\Network\Exception;


use Throwable;

class RequestException extends \Exception
{
    const ERRNO_NETWORK_ERROR = 10001;

    const ERROR_DATA_PARSE_ERROR = 10002;

    private $rawResponse;

    public function __construct(string $message = "", int $code = 0, $rawResponse = null)
    {
        parent::__construct($message, $code);

        $this->rawResponse = $rawResponse;
    }

    public function getRawResponse()
    {
        return $this->rawResponse;
    }
}
