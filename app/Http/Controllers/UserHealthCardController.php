<?php

namespace App\Http\Controllers;

use App\UserHealthCard;
use App\Utils\Views;
use App\WeChatWork\SessionUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserHealthCardController extends Controller
{
    public function store(SessionUtils $sessionUtils, Request $request)
    {
        $this->validate($request, [
            "phone" => "required|numeric|digits_between:8,14",
            "address" => "required|max:512",
            "stayed_in_key_places" => "required",
            "touched_high_risk_people" => "required",
        ], [], [
            "address" => "住址",
            "stayed_in_key_places" => "假期是否曾前往疫情防控重点地区",
            "touched_high_risk_people" => "是否接触过疫情防控重点地区高危人员",
        ]);

        $values = [
            "user_id" => $sessionUtils->getUser()->id,
            "phone" => $request->phone,
            "address" => $request->address,
        ];

        if ($request->stayed_in_key_places) {
            $this->validate($request, [
                "in_key_places_from" => "required|date_format:Y-m-d",
                "in_key_places_to" => "nullable|date_format:Y-m-d",
                "back_to_dongguan_at" => "nullable|date_format:Y-m-d",
            ], [], [
                "in_key_places_from" => "前往时间",
                "in_key_places_to" => "离开时间",
                "back_to_dongguan_at" => "返莞时间",
            ]);
            $values["in_key_places_from"] = $request->in_key_places_from;
            $values["in_key_places_to"] = $request->in_key_places_to;
            $values["back_to_dongguan_at"] = $request->back_to_dongguan_at;
        }
        if ($request->touched_high_risk_people) {
            $this->validate($request, [
                "touched_high_risk_people_at" => "required|date_format:Y-m-d",
            ], [], [
                "touched_high_risk_people_at" => "接触时间",
            ]);
            $values["touched_high_risk_people_at"] = $request->touched_high_risk_people_at;
        }

        DB::transaction(function () use (&$values, $sessionUtils) {
            UserHealthCard::query()->where("user_id", $sessionUtils->getUser()->id)->delete();
            UserHealthCard::query()->create($values);
        });

        return Views::successAPIResponse();
    }
}
