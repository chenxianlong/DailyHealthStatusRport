<?php


namespace App\WeChatWork;


use App\WeChatWork\Contract\AccessTokenRefreshHook;
use App\WeChatWork\Contract\AccessTokenRepository;

class StoreNewAccessToken implements AccessTokenRefreshHook
{
    private $applicationId;

    private $accessTokenRepository;

    public function __construct($applicationId, AccessTokenRepository $accessTokenRepository)
    {
        $this->applicationId = $applicationId;
        $this->accessTokenRepository = $accessTokenRepository;
    }

    public function beforeAccessTokenRefresh($currentAccessToken)
    {
        $lockedAccessToken = $this->accessTokenRepository->lockAccessTokenForUpdateByApplicationId($this->applicationId);
        if ($this->isAccessTokenUpdated($lockedAccessToken, $currentAccessToken)) {
            return $lockedAccessToken;
        }
        return null;
    }

    public function afterAccessTokenRefresh(AccessToken $accessToken)
    {
        $this->accessTokenRepository->updateAccessTokenByApplicationId($this->applicationId, $accessToken);
    }

    public function onSucceed()
    {
        $this->accessTokenRepository->commitAccessToken();
    }

    public function onException(\Throwable $throwable)
    {
        $this->accessTokenRepository->rollbackAccessToken();
    }

    private function isAccessTokenUpdated(AccessToken $lockedAccessToken, $accessTokenBeforeLock): bool
    {
        return !empty($lockedAccessToken->accessToken) && $lockedAccessToken->accessToken !== $accessTokenBeforeLock && time() < $lockedAccessToken->expireAt;
    }
}
