<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-1
 * Time: 下午11:14
 */

namespace App\Network\Contract;


use App\Network\Exception\RequestException;

interface Request
{
    /**
     * @return string|false
     */
    public function response(&$error = null, &$errno = null);

    /**
     * @return mixed
     * @throws RequestException
     */
    public function responseOrFail();

    /**
     * @param bool $assoc
     * @param mixed $rawResponse
     * @return mixed
     * @throws RequestException
     */
    public function JSONResponse($assoc = false, &$rawResponse = null);
}
