<?php

namespace App\Http\Controllers;

use App\User;
use App\Utils\Views;
use App\WeChatWork\SessionUtils;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function bindIDCardNo()
    {
        $sessionUtils = new SessionUtils($this->request->session(), env("HR_APPLICATION_ID"));
        $userId = $sessionUtils->getUserId();
        $openId = $sessionUtils->getOpenId();
        if (empty($userId) && empty($openId)) {
            return ["result" => false, "message" => "无法获取用户身份信息"];
        }

        $idCardNo = $this->request->idCardNo;
        $user = User::query()->where("id_card_no", $idCardNo)->first();
        if (is_null($user) && strlen($idCardNo) === 8) {
            $user = User::query()->where("id_card_no", $idCardNo . "（台胞证）")->first();
        }
        if (is_null($user)) {
            return ["result" => false, "message" => "此身份证号码不存在"];
        }
        if (!empty($user->open_id) || !empty($user->user_id)) {
            return ["result" => false, "message" => "此身份证号码已被另一微信号绑定"];
        }
        if (empty($this->request->name)) {
            return Views::successAPIResponse([
                "name" => $user->name,
            ]);
        }
        if (empty($openId)) {
            $user->update(["user_id" => $userId]);
        } else {
            $user->update(["open_id" => $openId]);
        }
        $sessionUtils->setUser($user);
        return Views::successAPIResponse();
    }
}
