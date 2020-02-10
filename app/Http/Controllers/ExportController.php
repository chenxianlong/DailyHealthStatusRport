<?php

namespace App\Http\Controllers;

use App\Constants\AvailableAttributes;
use App\User;
use App\UserDailyHealthStatus;
use App\UserHealthCard;
use App\UserHealthReports;
use App\Utils\Views;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ExportController extends Controller
{
    private $request;

    private $storeDirectory;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->storeDirectory = sys_get_temp_dir() . "/health-report-export/";
        @mkdir($this->storeDirectory, 0700);
    }

    public function status()
    {
        if (!$this->request->session()->get("export.authenticated", false)) {
            return Views::successAPIResponse([
                "authenticated" => false,
                "availableDates" => [],
            ]);
        }
        return Views::successAPIResponse([
            "authenticated" => true,
            "availableDates" => UserDailyHealthStatus::query()->groupBy("reported_date")->pluck("reported_date", "reported_date"),
        ]);
    }

    public function authenticate()
    {
        if (!password_verify($this->request->password, env("HR_EXPORT_PASSWORD"))) {
            throw ValidationException::withMessages(["password" => "访问密码错误"]);
        }
        $this->request->session()->put("export.authenticated", true);
        return Views::successAPIResponse([
            "authenticated" => true,
            "availableDates" => UserDailyHealthStatus::query()->groupBy("reported_date")->pluck("reported_date", "reported_date"),
        ]);
    }

    public function exportAll()
    {
        if (!$this->request->session()->get("export.authenticated", false)) {
            abort(403);
        }

        $this->validate($this->request, [
            "type" => [
                "required",
                Rule::in([-1, 0, 1, 2]),
            ],
        ]);

        $availableDatesQueryBuilder = UserDailyHealthStatus::query();
        if ($this->request->type != -1) {
            $availableDatesQueryBuilder->join("users", "user_daily_health_statuses.user_id", "=", "users.id")->where("users.type", $this->request->type);
        }
        $availableDates = $availableDatesQueryBuilder->groupBy("reported_date")->orderBy("reported_date")->pluck("reported_date")->toArray();

        $dateCount = count($availableDates);

        $userDailyHealthStatusesQueryBuilder = UserDailyHealthStatus::query();
        if ($this->request->type != -1) {
            $userDailyHealthStatusesQueryBuilder->join("users", "user_daily_health_statuses.user_id", "=", "users.id")->where("users.type", $this->request->type);
        }
        $userDailyHealthStatuses = $userDailyHealthStatusesQueryBuilder->select("user_daily_health_statuses.*")->get()->groupBy("user_id");

        $filename = "all-" . time() . mt_rand(100000000, 999999999) . ".xls";
        $filePath = $this->storeDirectory . $filename;
        $fp = fopen($filePath, "w");

        ini_set("max_execution_time", 60);

        fwrite($fp, <<<EOF
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8">
<style>
    .text{
        mso-number-format:"\@";
    }
</style>
</head>
<body>
EOF
        );
        fwrite($fp, <<<EOF
<table border="1">
<thead>
<tr>
    <th rowspan="2">姓名</th>
    <th rowspan="2">身份证号码</th>
    <th rowspan="2">联系电话</th>
    <th rowspan="2">现居住地址</th>
    <th rowspan="2">曾前往疫情防控重点地区</th>
    <th rowspan="2">前往时间</th>
    <th rowspan="2">离开时间</th>
    <th rowspan="2">返莞时间</th>
    <th rowspan="2">曾接触过疫情防疫重点地区高危人员</th>
    <th rowspan="2">接触时间</th>
    <th colspan="{$dateCount}">自身健康状况</th>
    <th colspan="{$dateCount}">家庭成员健康状况</th>
</tr>
<tr>
EOF
        );

        foreach ($availableDates as $date) {
            fwrite($fp, "<td>". substr($date, 0, 10) ."</td>");
        }
        foreach ($availableDates as $date) {
            fwrite($fp, "<td>". substr($date, 0, 10) ."</td>");
        }

        fwrite($fp, <<<EOF
</tr>
</thead>
<tbody>
EOF
        );

        foreach ($userDailyHealthStatuses as $userId => $statuses) {
            /**
             * @var Collection $statuses
             */
            $statusesKeyByDate = $statuses->keyBy("reported_date");
            $user = User::query()->find($userId);
            if (!$user) {
                continue;
            }
            $userHealthCard = UserHealthCard::query()->find($userId);
            $row = "<tr>";
            $row .= "<td class='text' rowspan='2'>". htmlentities($user->name) ."</td>";
            $row .= "<td class='text' rowspan='2'>". $user->id_card_no ."</td>";
            if ($userHealthCard) {
                $row .= "<td class='text' rowspan='2'>". $userHealthCard->phone ."</td>";
                $row .= "<td class='text' rowspan='2'>". $userHealthCard->address ."</td>";
                $row .= "<td class='text' rowspan='2'>". ($userHealthCard->in_key_places_from ? "是" : "否")  ."</td>";
                $row .= "<td class='text' rowspan='2'>". $userHealthCard->in_key_places_from ."</td>";
                $row .= "<td class='text' rowspan='2'>". $userHealthCard->in_key_places_to ."</td>";
                $row .= "<td class='text' rowspan='2'>". $userHealthCard->back_to_dongguan_at ."</td>";
                $row .= "<td class='text' rowspan='2'>". ($userHealthCard->touched_high_risk_people_at ? "是" : "否") ."</td>";
                $row .= "<td class='text' rowspan='2'>". $userHealthCard->touched_high_risk_people_at ."</td>";
            } else {
                $row .= "<td class='text' rowspan='2'></td>";
                $row .= "<td class='text' rowspan='2'></td>";
                $row .= "<td class='text' rowspan='2'></td>";
                $row .= "<td class='text' rowspan='2'></td>";
                $row .= "<td class='text' rowspan='2'></td>";
                $row .= "<td class='text' rowspan='2'></td>";
                $row .= "<td class='text' rowspan='2'></td>";
                $row .= "<td class='text' rowspan='2'></td>";
            }

            $selfRow1Columns = "";
            $familyRow1Columns = "";
            $selfRow2Columns = "";
            $familyRow2Columns = "";
            foreach ($availableDates as $date) {
                if ($statusesKeyByDate->has($date)) {
                    $status = $statusesKeyByDate->get($date);
                    $selfRow1Columns .= "<td class='text'>". ($status->self_status ? "异常" : "正常") ."</td>";
                    $selfRow2Columns .= "<td class='text'>". htmlentities($status->self_status_details) ."</td>";
                    $familyRow1Columns .= "<td class='text'>". ($status->family_status ? "异常" : "正常") ."</td>";
                    $familyRow2Columns .= "<td class='text'>". htmlentities($status->family_status_details) ."</td>";
                } else {
                    $selfRow1Columns .= "<td class='text'></td>";
                    $selfRow2Columns .= "<td class='text'></td>";
                    $familyRow1Columns .= "<td class='text'></td>";
                    $familyRow2Columns .= "<td class='text'></td>";
                }
            }
            fwrite($fp, $row);
            fwrite($fp, $selfRow1Columns);
            fwrite($fp, $familyRow1Columns);
            fwrite($fp, "</tr><tr>");
            fwrite($fp, $selfRow2Columns);
            fwrite($fp, $familyRow2Columns);
            fwrite($fp, "</tr>");
        }

        fwrite($fp, <<<EOF
</tbody>
</table>
</body>
</html>
EOF
        );

        fflush($fp);
        fclose($fp);

        return Views::successAPIResponse([
            "filename" => $filename,
            "expireAt" => $expireAt = (time() + 600),
            "salt" => $salt = base64_encode(random_bytes(8)),
            "signature" => sha1($filename . $expireAt . $salt . env("HR_EXPORT_PASSWORD")),
        ]);
            response($fullPageContent)
                ->header("Cache-Control", "no-cache, no-store, must-revalidate")
                ->header("Content-type", "application/vnd.ms-excel")
                ->header("Content-Disposition", "attachment; filename=all-". time() . ".xls")
            ;
    }

    public function exportNotReported()
    {
        if (!$this->request->session()->get("export.authenticated", false)) {
            abort(403);
        }

        $this->validate($this->request, [
            "date" => [
                "required",
                "date_format:Y-m-d",
            ],
            "type" => [
                "required",
                Rule::in([-1, 0, 1, 2]),
            ],
        ]);

        $date = $this->request->date;

        if ($this->request->type != -1) {
            $notReportedUsers = DB::select("SELECT * FROM `users` WHERE id NOT IN (SELECT user_id FROM `user_daily_health_statuses` WHERE reported_date = ?) AND type = ?", [$date, $this->request->type]);
        } else {
            $notReportedUsers = DB::select("SELECT * FROM `users` WHERE id NOT IN (SELECT user_id FROM `user_daily_health_statuses` WHERE reported_date = ?)", [$date]);
        }

        $filename = "not-reported-" . $date . "-" . time() . mt_rand(100000000, 999999999) . ".xls";
        $filePath = $this->storeDirectory . $filename;
        $fp = fopen($filePath, "w");

        ini_set("max_execution_time", 60);

        fwrite($fp, <<<EOF
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8">
<style>
    .text{
        mso-number-format:"\@";
    }
</style>
</head>
<body>
EOF
        );
        fwrite($fp, <<<EOF
<table border="1">
<thead>
<tr>
    <th>姓名</th>
    <th>身份证号码</th>
</tr>
</thead>
<tbody>
EOF
        );

        foreach ($notReportedUsers as $user) {
            fwrite($fp, "<tr>");
            fwrite($fp, "<td class='text'>". $user->name ."</td><td class='text'>". $user->id_card_no ."</td>");
            fwrite($fp, "</tr>");
        }

        fwrite($fp, <<<EOF
</tbody>
</table>
</body>
</html>
EOF
        );

        fflush($fp);
        fclose($fp);

        return Views::successAPIResponse([
            "filename" => $filename,
            "expireAt" => $expireAt = (time() + 600),
            "salt" => $salt = base64_encode(random_bytes(8)),
            "signature" => sha1($filename . $expireAt . $salt . env("HR_EXPORT_PASSWORD")),
        ]);
        return
            response($fullPageContent)
                ->header("Cache-Control", "no-cache, no-store, must-revalidate")
                ->header("Content-type", "application/vnd.ms-excel")
                ->header("Content-Disposition", "attachment; filename=not-reported-". $date . ".xls")
            ;
    }

    public function download()
    {
        if (!hash_equals(sha1($this->request->filename . $this->request->expireAt . $this->request->salt . env("HR_EXPORT_PASSWORD")), $this->request->signature)) {
            abort(403, "参数验签不通过");
        }
        if (intval($this->request->expireAt) < time()) {
            abort(403, "链接已过期");
        }

        return response()->download($this->storeDirectory . $this->request->filename);
    }
}
