<?php
/**
 * Created by PhpStorm.
 * Date: 2019/12/13
 * Time: 上午11:23
 */

namespace App\Utils;


abstract class Views
{
    public static function mix($path)
    {
        return mix($path, config("app.debug") ? "development" : "");
    }

    public static function successAPIResponse($data = [])
    {
        return [
            "result" => true,
            "data" => $data,
        ];
    }
}
