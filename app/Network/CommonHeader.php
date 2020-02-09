<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-3
 * Time: 下午2:56
 */

namespace App\Network;


trait CommonHeader
{
    protected $commonHeaders = [];

    public function addCommonHeader($name, $value)
    {
        $this->commonHeaders[$name] = $value;
        return $this;
    }
}
