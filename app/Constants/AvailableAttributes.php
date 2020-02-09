<?php
/**
 * Created by PhpStorm.
 * Date: 2020/2/8
 * Time: 下午8:40
 */

namespace App\Constants;


interface AvailableAttributes
{
    const AVAILABLE_ATTRIBUTES = [
        "native_place" => "籍贯",
        "permanent_place" => "户籍地址",
        "address" => "家庭住址",
        "current_place" => "目前所在区域",
        "from_hb_in_14" => "最近14天是否曾到过湖北",
        "phone" => "手机号码",
        "emergency_contact" => "紧急联系人",
        "emergency_contact_phone" => "紧急联系人电话",
        "current_health_status" => "目前身体健康状况",
        "touched_from_hb_in14" => "最近14天是否接触过“近14日出入过湖北地区的人员”",
        "touched_suspected" => "最近14天是否接触过疑似病例/确诊病例”",
        "recently_leave_dg" => "最近14天内离莞出行情况",
        "by_long_distance_transport" => "有无乘坐长途公共交通工具",
        "is_key_people" => "是否为重点人群",
        "is_live_key_people" => "同住家庭成员有无重点人群",
        "remark" => "备注",
    ];

    const BOOLEAN_ATTRIBUTE = [
        "from_hb_in_14" => true,
        "touched_from_hb_in14" => true,
        "touched_suspected" => true,
        "by_long_distance_transport" => true,
        "is_key_people" => true,
        "is_live_key_people" => true,
    ];
}
