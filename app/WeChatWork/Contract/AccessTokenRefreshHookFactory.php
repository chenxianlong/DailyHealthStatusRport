<?php


namespace App\WeChatWork\Contract;


interface AccessTokenRefreshHookFactory
{
    public function make($applicationId): AccessTokenRefreshHook;
}
