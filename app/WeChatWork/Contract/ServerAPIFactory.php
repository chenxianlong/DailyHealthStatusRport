<?php


namespace App\WeChatWork\Contract;


interface ServerAPIFactory
{
    public function make($applicationId): ServerAPI;
}
