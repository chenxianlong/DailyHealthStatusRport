<?php

namespace App\Http\Controllers;

use App\User;
use App\UserDailyHealthStatus;
use App\UserHealthCard;
use App\Utils\Views;
use App\WeChatWork\Contract\ServerAPIFactory;
use App\WeChatWork\Exception\WechatWorkAPIException;
use App\WeChatWork\SessionUtils;
use Illuminate\Http\Request;

class UserDailyHealthStatusController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function showForm(SessionUtils $sessionUtils, ServerAPIFactory $serverAPIFactory)
    {
        if ($sessionUtils->getUser()) {
            return SPAController::SPAView();
        }

        if ($sessionUtils->getUserId()) {
            try {
                $userInformation = $serverAPIFactory->make(env("HR_APPLICATION_ID"))->getUser($sessionUtils->getUserId());
                $idCardNo = null;
                foreach ($userInformation->extattr->attrs as $attr) {
                    if ($attr->name === "证件号") {
                        $idCardNo = $attr->value;
                        break;
                    }
                }
                if (empty($idCardNo)) {
                    return $this->redirect2Bind();
                }
                $user = User::query()->where("id_card_no", $idCardNo)->first();
                if (is_null($user)) {
                    return $this->redirect2Bind();
                }
                $user->update(["user_id" => $sessionUtils->getUserId()]);
                return SPAController::SPAView();
            } catch (WechatWorkAPIException $wechatWorkAPIException) {
                return $this->redirect2Bind();
            }
        }
        return $this->redirect2Bind();
    }

    public function status(SessionUtils $sessionUtils)
    {
        return Views::successAPIResponse([
            "name" => $sessionUtils->getUser()->name,
            "hasHealthCard" => !is_null(UserHealthCard::query()->find($sessionUtils->getUser()->id)),
            "todayReported" => !is_null(UserDailyHealthStatus::query()->where("user_id", $sessionUtils->getUser()->id)->where("reported_date", date("Y-m-d"))->first()),
        ]);
    }

    public function store(SessionUtils $sessionUtils)
    {
        $this->validate($this->request, [
            "self_status" => "required",
            "family_status" => "required",
        ], [], [
            "self_status" => "自身健康状况",
            "family_status" => "家庭成员健康状况",
        ]);

        $values = [
            "reported_date" => date("y-m-d"),
            "user_id" => $sessionUtils->getUser()->id,
            "self_status" => intval(!!$this->request->self_status),
            "family_status" => intval(!!$this->request->family_status),
        ];

        if ($values["self_status"]) {
            $this->validate($this->request, [
                "self_status_details" => "required|max:255",
            ], [], [
                "self_status_details" => "自身异常症状",
            ]);
            $values["self_status_details"] = $this->request->self_status_details;
        }

        if ($values["family_status"]) {
            $this->validate($this->request, [
                "family_status_details" => "required|max:255",
            ], [], [
                "family_status_details" => "自身异常症状",
            ]);
            $values["family_status_details"] = $this->request->family_status_details;
        }

        UserDailyHealthStatus::query()->create($values);

        return Views::successAPIResponse();
    }

    private function redirect2Bind()
    {
        return redirect(route("users.bind"));
    }
}
