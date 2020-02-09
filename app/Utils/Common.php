<?php


namespace App\Utils;


use Illuminate\Support\Facades\Log;

class Common
{
    public static function logException(\Throwable $throwable)
    {
        Log::error($throwable->getMessage(), ["exception" => $throwable]);
    }
}
