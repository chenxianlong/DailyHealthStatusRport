<?php
/**
 * Created by PhpStorm.
 * Date: 2019/12/12
 * Time: 上午8:52
 */

namespace App\Http\Middleware\WeChatWork;


use App\Utils\Common;
use App\WeChatWork\Contract\ServerAPI;
use App\WeChatWork\Contract\ServerAPIFactory;
use App\WeChatWork\Exception\WechatWorkAPIException;
use App\WeChatWork\SessionUtils;
use Illuminate\Http\Request;

class WechatWork
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var SessionUtils $sessionUtils
     */
    protected $sessionUtils;

    protected $applicationId;

    protected function redirect2Wechat()
    {
        $currentURL = $this->request->fullUrl();
        $this->request->session()->put("wechat_work.history_url", $currentURL);
        $userAgent = $this->request->userAgent();
        if (stristr($userAgent, "wxwork/") === false && stristr($userAgent, "MicroMessenger") === false) {
            return redirect($this->makeServerAPI()->generateQRConnectURL($currentURL));
        }
        return redirect($this->makeServerAPI()->generateOAuthURL($currentURL));
    }

    protected function getUserInfo(): bool
    {
        $code = $this->request->code;
        $serverAPI = $this->makeServerAPI();
        $userInformation = $serverAPI->getUserInformation($code);
        if (property_exists($userInformation, "UserId")) {
            $this->sessionUtils->setUserId($userInformation->UserId);
            try {
                $user = $serverAPI->getUser($userInformation->UserId);
                $this->sessionUtils->setUser($user);
            } catch (WechatWorkAPIException $e) {
                Common::logException($e);
            }
        } else if (property_exists($userInformation, "OpenId")) {
            $this->sessionUtils->setOpenId($userInformation->OpenId);
        } else {
            abort(500, "暂时无法提供服务");
        }
        return true;
    }

    protected function makeServerAPI(): ServerAPI
    {
        $serverAPIFactory = resolve(ServerAPIFactory::class);
        return $serverAPIFactory->make($this->applicationId);
    }

    protected function back2HistoryURL()
    {
        return redirect($this->request->session()->get("wechat_work.history_url"));
    }
}
