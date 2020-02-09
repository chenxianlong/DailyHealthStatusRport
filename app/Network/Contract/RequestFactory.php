<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-3
 * Time: 下午2:49
 */

namespace App\Network\Contract;


use App\Network\Contract\Request;

interface RequestFactory
{
    /**
     * @param mixed $name
     * @param mixed $value
     * @return static
     */
    public function addCommonHeader($name, $value);

    /**
     * @param string $name
     * @param $value
     * @return static
     */
    public function addCommonOption($name, $value);

    /**
     * @param string $path
     * @param null|mixed $data
     * @param null|array $headers
     * @param null|callable $setOptions
     * @return Request
     */
    public function make($path, $data = null, $headers = null, $setOptions = null);
}
