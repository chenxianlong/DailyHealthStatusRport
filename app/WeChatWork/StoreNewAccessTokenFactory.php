<?php
declare(strict_types=1);

namespace App\WeChatWork;


use App\WeChatWork\Contract\AccessTokenRefreshHook;
use App\WeChatWork\Contract\AccessTokenRefreshHookFactory;
use App\WeChatWork\Contract\AccessTokenRepository;

class StoreNewAccessTokenFactory implements AccessTokenRefreshHookFactory
{
    private $accessTokenRepository;

    public function __construct(AccessTokenRepository $accessTokenRepository)
    {
        $this->accessTokenRepository = $accessTokenRepository;
    }

    public function make($applicationId): AccessTokenRefreshHook
    {
        return new StoreNewAccessToken($applicationId, $this->accessTokenRepository);
    }
}
