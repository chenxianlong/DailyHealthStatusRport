<?php
declare(strict_types=1);

namespace App\WeChatWork;


use App\Network\Contract\RequestFactory;
use App\Network\Exception\RequestException;
use App\WeChatWork\Contract\AccessTokenRefreshHook;
use App\WeChatWork\Contract\ServerAPI;
use App\WeChatWork\Exception\WechatWorkAPIException;

class HTTPServerAPI implements ServerAPI
{
    private $requestFactory;

    private $appId;

    private $agentId;

    private $secret;

    /**
     * @var AccessToken|null
     */
    private $accessToken;

    /**
     * @var AccessTokenRefreshHook $accessTokenRefreshHook
     */
    private $accessTokenRefreshHook;

    public function __construct(RequestFactory $requestFactory, $appId = null, $agentId = null, $secret = null, $accessToken = null)
    {
        $this->requestFactory = $requestFactory;

        $this->appId = $appId;
        $this->agentId = $agentId;
        $this->secret = $secret;
        if (is_null($accessToken)) {
            $accessToken = new AccessToken();
        }
        $this->accessToken = $accessToken;
    }

    public function setAppId(string $appId): ServerAPI
    {
        $this->appId = $appId;
        return $this;
    }

    public function setAgentId(string $agentId): ServerAPI
    {
        $this->agentId = $agentId;
        return $this;
    }

    public function setSecret(string $secret): ServerAPI
    {
        $this->secret = $secret;
        return $this;
    }

    public function setAccessToken(AccessToken $accessToken): ServerAPI
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function setAccessTokenRefreshHook(AccessTokenRefreshHook $accessTokenRefreshHook): ServerAPI
    {
        $this->accessTokenRefreshHook = $accessTokenRefreshHook;
        return $this;
    }

    public function getAccessToken(): AccessToken
    {
        $response = $this->sendAPIGetRequest("/cgi-bin/gettoken", [
            "corpid" => $this->appId,
            "corpsecret" => $this->secret,
        ], true);
        $accessToken = new AccessToken();
        $accessToken->accessToken = $response->access_token;
        $accessToken->expireAt = time() + $response->expires_in - 60;
        return $accessToken;
    }

    public function generateOAuthURL(string $redirectURL, string $state = null): string
    {
        $params = [
            "appid" => $this->appId,
            "redirect_uri" => $redirectURL,
            "response_type" => "code",
            "scope" => "snsapi_base",
        ];
        if (!empty($state)) {
            $params["state"] = $state;
        }
        return sprintf("%s?%s#wechat_redirect", "https://open.weixin.qq.com/connect/oauth2/authorize", http_build_query($params));
    }

    public function generateQRConnectURL(string $redirectURL, string $state = null): string
    {
        $params = [
            "appid" => $this->appId,
            "agentid" => $this->agentId,
            "redirect_uri" => $redirectURL,
        ];
        if (!empty($state)) {
            $params["state"] = $state;
        }
        return sprintf("%s?%s", "https://open.work.weixin.qq.com/wwopen/sso/qrConnect", http_build_query($params));
    }

    public function getUserInformation(string $code): object
    {
        return $this->sendAPIGetRequest("/cgi-bin/user/getuserinfo", [
            "code" => $code,
        ]);
    }

    public function getUser(string $userId): object
    {
        return $this->sendAPIGetRequest("/cgi-bin/user/get", [
            "userid" => $userId,
        ]);
    }

    public function updateUser(string $userId, array $values)
    {
        $values["userid"] = $userId;
        return $this->sendAPIPostRequest("/cgi-bin/user/update", $values);
    }

    public function sendMessage($msgtype, $message, $touser = null, $toparty = null, $totag = null): object
    {
        $message["touser"] = $touser ?? "";
        $message["toparty"] = $toparty ?? "";
        $message["totag"] = $totag ?? "";
        $message["msgtype"] = $msgtype;
        $message["agentid"] = $this->agentId;
        return $this->sendAPIPostRequest("/cgi-bin/message/send", $message);
    }

    public function getDepartmentList($id = null): array
    {
        return $this->sendAPIGetRequest("/cgi-bin/department/list", [
            "id" => $id,
        ])->department;
    }

    public static function generateURL($uri, array $params = null): string
    {
        $url = "https://qyapi.weixin.qq.com" . $uri;
        if (!is_null($params)) {
            $url .= "?" . http_build_query($params);
        }
        return $url;
    }

    private function sendAPIRequest($uri, array $getParams = null, array $postParams = null, $postAsJSON = false, $noAccessTokenRequirement = false): object
    {
        if ($noAccessTokenRequirement === false) {
            if (is_null($getParams)) {
                $getParams = [];
            }
            $getParams["access_token"] = $this->accessToken->accessToken;
        }
        $url = self::generateURL($uri, $getParams);
        try {
            $response = $this->makeRequest($url, $postParams, $postAsJSON)->JSONResponse();
            if ($response->errcode === 40014 || $response->errcode === 41001 || $response->errcode === 42001) {
                // refresh access token and then try again
                $this->refreshAccessToken();
                $getParams["access_token"] = $this->accessToken->accessToken;
                $url = self::generateURL($uri, $getParams);
                $response = $this->makeRequest($url, $postParams, $postAsJSON)->JSONResponse();
                if ($response->errcode !== 0) {
                    throw new WechatWorkAPIException($response->errmsg, $response->errcode);
                }
            } else if ($response->errcode !== 0) {
                throw new WechatWorkAPIException($response->errmsg, $response->errcode);
            }
            return $response;
        } catch (RequestException $e) {
            throw new WechatWorkAPIException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function sendAPIGetRequest($uri, array $params = null, $noAccessTokenRequirement = false): object
    {
        return $this->sendAPIRequest($uri, $params, null, false, $noAccessTokenRequirement);
    }

    private function sendAPIPostRequest($uri, array $params = null, $postAsJSON = true): object
    {
        return $this->sendAPIRequest($uri, null, $params, $postAsJSON);
    }

    private function makeRequest($url, $postParams, $postAsJSON)
    {
        if ($postAsJSON && !is_null($postParams)) {
            $request = $this->requestFactory->make($url, json_encode($postParams), [
                "Content-Type" => "application/json",
            ]);
        } else {
            $request = $this->requestFactory->make($url, $postParams);
        }
        return $request;
    }

    private function refreshAccessToken()
    {
        if ($this->accessTokenRefreshHook) {
            try {
                // validate if access token already refreshed by other process
                $newAccessToken = $this->accessTokenRefreshHook->beforeAccessTokenRefresh($this->accessToken->accessToken);
                if (is_null($newAccessToken)) {
                    // get new one via server api and invoke hook
                    $this->accessToken = $this->getAccessToken();
                    $this->accessTokenRefreshHook->afterAccessTokenRefresh($this->accessToken);
                } else {
                    $this->accessToken = $newAccessToken;
                }
                $this->accessTokenRefreshHook->onSucceed();
            } catch (\Throwable $e) {
                $this->accessTokenRefreshHook->onException($e);
                throw $e;
            }
        } else {
            $this->accessToken = $this->getAccessToken();
        }
    }
}
