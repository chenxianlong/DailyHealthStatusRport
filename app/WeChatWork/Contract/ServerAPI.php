<?php
declare(strict_types=1);

namespace App\WeChatWork\Contract;


use App\WeChatWork\AccessToken;
use App\WeChatWork\Exception\WechatWorkAPIException;

interface ServerAPI
{
    public function setAppId(string $appId): ServerAPI;

    public function setAgentId(string $agentId): ServerAPI;

    public function setSecret(string $secret): ServerAPI;

    public function setAccessToken(AccessToken $accessToken): ServerAPI;

    public function setAccessTokenRefreshHook(AccessTokenRefreshHook $accessTokenRefreshHook): ServerAPI;

    /**
     * @return AccessToken
     * @throws WechatWorkAPIException
     */
    public function getAccessToken(): AccessToken;

    public function generateOAuthURL(string $redirectURL, string $state = null): string;

    public function generateQRConnectURL(string $redirectURL, string $state = null): string;

    public function getUserInformation(string $code): object;

    public function getUser(string $userId): object;

    public function updateUser(string $userId, array $values);

    public function sendMessage($msgtype, $message, $touser = null, $toparty = null, $totag = null): object;

    public function getDepartmentList($id = null): array;
}
