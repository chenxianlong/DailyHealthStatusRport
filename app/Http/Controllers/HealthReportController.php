<?php

namespace App\Http\Controllers;

use App\Constants\AvailableAttributes;
use App\User;
use App\UserHealthReports;
use App\Utils\Views;
use App\WeChatWork\Contract\ServerAPIFactory;
use App\WeChatWork\Exception\WechatWorkAPIException;
use App\WeChatWork\SessionUtils;
use Illuminate\Http\Request;

class HealthReportController extends Controller
{
    private $request;

    private $sessionUtils;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function showForm(ServerAPIFactory $serverAPIFactory)
    {
        $sessionUtils = $this->getSessionUtils();
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

    public function status()
    {
        $sessionUtils = $this->getSessionUtils();
        $userId = $sessionUtils->getUser()->id;
        $reported = UserHealthReports::query()->where("user_id", $userId)->where("reported_date", date("Y-m-d"))->first();
        $latestReport = null;
        if ($latestReportDate = UserHealthReports::query()->where("user_id", $userId)->orderByDesc("reported_date")->first()) {
            $latestReport = UserHealthReports::query()->where("user_id", $userId)->where("reported_date", $latestReportDate->reported_date)->get()->pluck("value", "field");
        }
        return Views::successAPIResponse([
            "reported" => !is_null($reported),
            "name" => $this->getSessionUtils()->getUser()->name,
            "latestReport" => $latestReport,
        ]);
    }

    public function store()
    {
        $date = date("Y-m-d");
        if (UserHealthReports::query()->where("user_id", $userId = $this->getSessionUtils()->getUser()->id)->where("reported_date", $date)->first()) {
            return ["result" => false, "message" => "暂无需上报信息"];
        }
        $this->validate($this->request, [
            "native_place" => "required|max:32",
            "permanent_place" => "required|max:512",
            "address" => "required|max:512",
            "current_place" => "required|max:32",
            "from_hb_in_14" => "required",
            "phone" => "required|numeric|digits_between:8,14",
            "emergency_contact" => "required|max:16",
            "emergency_contact_phone" => "required|numeric|digits_between:8,14",
            "current_health_status" => "required|max:16",
            "touched_from_hb_in14" => "required",
            "touched_suspected" => "required",
            "recently_leave_dg" => "required|max:255",
            "by_long_distance_transport" => "required",
            "is_key_people" => "required",
            "is_live_key_people" => "required",
            "remark" => "nullable|max:512",
        ], [], AvailableAttributes::AVAILABLE_ATTRIBUTES);

        $values = [];
        foreach (AvailableAttributes::AVAILABLE_ATTRIBUTES as $attributeKey => $displayText) {
            $value = $this->request->input($attributeKey);
            if (array_key_exists($attributeKey, AvailableAttributes::BOOLEAN_ATTRIBUTE)) {
                $value = intval(!!$value);
            }
            $values[] = [
                "reported_date" => $date,
                "user_id" => $userId,
                "field" => $attributeKey,
                "value" => $value,
            ];
        }

        UserHealthReports::query()->insert($values);
        return Views::successAPIResponse();
    }

    private function redirect2Bind()
    {
        return redirect(route("users.bind"));
    }

    private function getSessionUtils()
    {
        if (is_null($this->sessionUtils)) {
            $this->sessionUtils = new SessionUtils($this->request->session(), env("HR_APPLICATION_ID"));
        }
        return $this->sessionUtils;
    }
}
