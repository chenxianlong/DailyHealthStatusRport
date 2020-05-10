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
use Illuminate\Support\Facades\DB;

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
        $user = $sessionUtils->getUser();
        return Views::successAPIResponse([
            "name" => $user->name,
            "department" => $user->department,
            "type" => $user->type,
            "hasHealthCard" => UserHealthCard::query()->find($sessionUtils->getUser()->id),
            "todayReported" => !is_null(UserDailyHealthStatus::query()->where("user_id", $sessionUtils->getUser()->id)->where("reported_date", date("Y-m-d"))->first()),
        ]);
    }

    public function store(SessionUtils $sessionUtils)
    {
        $request = $this->request;

        $this->validate($this->request, [
            "card.phone" => "required|numeric|digits_between:8,14",
            "card.address" => "required|max:512",
            "status.self_status" => "required",
            "status.family_status" => "required",
        ], [], [
            "card.address" => "住址",
            "status.self_status" => "自身健康状况",
            "status.family_status" => "家庭成员健康状况",
        ]);

        $cardValues = [
            "user_id" => $sessionUtils->getUser()->id,
            "phone" => $this->request->card["phone"],
            "address" => $this->request->card["address"],
        ];

        $values = [
            "reported_date" => date("Y-m-d"),
            "user_id" => $sessionUtils->getUser()->id,
            "self_status" => intval(!!$this->request->status["self_status"]),
            "family_status" => intval(!!$this->request->status["family_status"]),
        ];

        if ($sessionUtils->getUser()->type === 0) {
            $this->validate($this->request, [
                "card.dorm_room" => "required|max:32",
            ], [
                "card.dorm_room" => "宿舍床位号",
            ]);
            $cardValues["dorm_room"] = $this->request->card["dorm_room"];
        } else if ($sessionUtils->getUser()->type === 1) {
            $this->validate($this->request, [
                "status.extra.today_touce_risk_people" => "required|integer|min:0|max:1",
                "status.extra.today_work_in_school" => "required|integer|min:0|max:1"
            ], [
                "today_touce_risk_people" => "本人或同住家庭成员当日是否接触过确诊病例、疑是病例或无症状感染者",
                "today_work_in_school" => "当日是否在校上班",
            ]);
            $values["extra"] = json_encode([
                "today_touce_risk_people" => $this->request->status["extra"]["today_touce_risk_people"],
                "today_work_in_school" => $this->request->status["extra"]["today_work_in_school"],
            ]);
        }

        if (@$request->card["stayed_in_key_places"]) {
            $this->validate($request, [
                "card.in_key_places_from" => "required|date_format:Y-m-d",
                "card.in_key_places_to" => "nullable|date_format:Y-m-d",
                "card.back_to_dongguan_at" => "nullable|date_format:Y-m-d",
            ], [], [
                "card.in_key_places_from" => "前往时间",
                "card.in_key_places_to" => "离开时间",
                "card.back_to_dongguan_at" => "返莞时间",
            ]);
            $cardValues["in_key_places_from"] = $request->card["in_key_places_from"];
            $cardValues["in_key_places_to"] = $request->card["in_key_places_to"];
            $cardValues["back_to_dongguan_at"] = $request->card["back_to_dongguan_at"];
        }
        if (@$request->card["touched_high_risk_people"]) {
            $this->validate($request, [
                "card.touched_high_risk_people_at" => "required|date_format:Y-m-d",
            ], [], [
                "card.touched_high_risk_people_at" => "接触时间",
            ]);
            $cardValues["touched_high_risk_people_at"] = $request->card["touched_high_risk_people_at"];
        }

        if ($values["self_status"]) {
            $this->validate($this->request, [
                "status.self_status_details" => "required|max:255",
            ], [], [
                "status.self_status_details" => "自身异常症状",
            ]);
            $values["self_status_details"] = $this->request->status["self_status_details"];
        }

        if ($values["family_status"]) {
            $this->validate($this->request, [
                "status.family_status_details" => "required|max:255",
            ], [], [
                "status.family_status_details" => "家庭成员异常症状",
            ]);
            $values["family_status_details"] = $this->request->status["family_status_details"];
        }

        DB::transaction(function () use (&$cardValues, &$values, $sessionUtils) {
            UserHealthCard::query()->where("user_id", $sessionUtils->getUser()->id)->delete();
            UserHealthCard::query()->create($cardValues);
            UserDailyHealthStatus::query()->create($values);
        });


        return Views::successAPIResponse();
    }

    private function redirect2Bind()
    {
        return redirect(route("users.bind"));
    }
}
