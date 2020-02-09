<?php


namespace App\WeChatWork\Contract;


use App\WeChatWork\AccessToken;

interface AccessTokenRefreshHook
{
    /**
     * @param string|null $currentAccessToken
     * @return AccessToken|null
     */
    public function beforeAccessTokenRefresh($currentAccessToken);

    public function afterAccessTokenRefresh(AccessToken $accessToken);

    public function onSucceed();

    public function onException(\Throwable $throwable);
}
