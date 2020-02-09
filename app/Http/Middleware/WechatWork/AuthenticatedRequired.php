<?php
declare(strict_types=1);

namespace App\Http\Middleware\WechatWork;

use App\User;
use App\Utils\Common;
use App\WeChatWork\Contract\ServerAPI;
use App\WeChatWork\Contract\ServerAPIFactory;
use App\WeChatWork\Exception\WechatWorkAPIException;
use App\WeChatWork\SessionUtils;
use Closure;
use Illuminate\Http\Request;

class AuthenticatedRequired extends WechatWork
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|int $applicationId
     * @return mixed
     */
    public function handle($request, Closure $next, $applicationId)
    {
        $this->request = $request;
        $this->sessionUtils = $sessionUtils = new SessionUtils($request->session(), $applicationId);
        $this->applicationId = $applicationId;

        if ($request->has("code")) {
            try {
                if ($this->getUserInfo()) {
                    return $this->back2HistoryURL();
                } else {
                    abort(403, "授权失败");
                }
            } catch (WechatWorkAPIException $wechatWorkAPIException) {
                Common::logException($wechatWorkAPIException);
                abort(403, "无法获取用户身份");
            }
        }

        if ($sessionUtils->getUserId() || $sessionUtils->getOpenId()) {
            if ($sessionUtils->getUserId()) {
                $sessionUtils->setUser(User::query()->where("user_id", $sessionUtils->getUserId())->first());
            } else {
                $sessionUtils->setUser(User::query()->where("open_id", $sessionUtils->getOpenId())->first());
            }
            return $next($request);
        } else if (!$request->expectsJson()) {
            return $this->redirect2Wechat();
        }
        abort(403);
    }
}
