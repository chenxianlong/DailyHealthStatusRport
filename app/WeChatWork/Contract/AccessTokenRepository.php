<?php


namespace App\WeChatWork\Contract;


use App\WeChatWork\AccessToken;

interface AccessTokenRepository
{
    /**
     * @param $applicationId
     * @return AccessToken
     */
    public function getAccessTokenByApplicationId($applicationId): AccessToken;

    /**
     * @param $applicationId
     * @return AccessToken the locked access token
     */
    public function lockAccessTokenForUpdateByApplicationId($applicationId): AccessToken;

    /**
     * @param $applicationId
     * @param AccessToken
     * @return mixed
     */
    public function updateAccessTokenByApplicationId($applicationId, AccessToken $accessToken);

    public function commitAccessToken();

    public function rollbackAccessToken();
}
