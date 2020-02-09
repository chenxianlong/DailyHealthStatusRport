<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-3
 * Time: 下午2:57
 */

namespace App\Network;


trait CommonOption
{
    protected $commonOptions = [];

    public function addCommonOption($name, $value)
    {
        $this->commonOptions[$name] = $value;
        return $this;
    }
}
