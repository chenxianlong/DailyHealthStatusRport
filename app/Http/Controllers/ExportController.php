<?php

namespace App\Http\Controllers;

use App\Constants\AvailableAttributes;
use App\User;
use App\UserHealthReports;
use App\Utils\Views;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExportController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
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
            "availableDates" => UserHealthReports::query()->groupBy("reported_date")->pluck("reported_date", "reported_date"),
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
            "availableDates" => UserHealthReports::query()->groupBy("reported_date")->pluck("reported_date", "reported_date"),
        ]);
    }

    public function exportAll()
    {
        $date = $this->request->date;
        if (!$this->request->session()->get("export.authenticated", false) || !preg_match('/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}$/', $date)) {
            abort(403);
        }
        $reports = UserHealthReports::query()->where("reported_date", $date)->get()->groupBy("user_id");

        $pageHead = <<<EOF
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
EOF;
        $tableStart = <<<EOF
<table border="1">
<thead>
<tr>
    <th>姓名</th>
    <th>身份证号码</th>
    <th>籍贯</th>
    <th>户籍地址</th>
    <th>家庭住址</th>
    <th>目前所在区域</th>
    <th>最近14天是否曾到过湖北</th>
    <th>手机号码</th>
    <th>紧急联系人</th>
    <th>紧急联系人电话</th>
    <th>目前身体健康状况</th>
    <th>最近14天是否接触过“近14日出入过湖北地区的人员”</th>
    <th>最近14天是否接触过疑似病例/确诊病例</th>
    <th>最近14天内离莞出行情况</th>
    <th>有无乘坐长途公共交通工具</th>
    <th>是否为重点人群</th>
    <th>同住家庭成员有无重点人群</th>
    <th>备注</th>
</tr>
</thead>
<tbody>
EOF;

        $tbodyRows = "";
        foreach ($reports as $userId => $fields) {
            $fieldsKeyByFieldName = $fields->keyBy("field");
            $user = User::query()->findOrFail($userId);
            $columns = "<td class='text'>". $user->name ."</td><td class='text'>". $user->id_card_no ."</td>";
            foreach (AvailableAttributes::AVAILABLE_ATTRIBUTES as $key => $value) {
                $valueText = htmlentities($fieldsKeyByFieldName[$key]->value);
                if (array_key_exists($key, AvailableAttributes::BOOLEAN_ATTRIBUTE)) {
                    $valueText = $fieldsKeyByFieldName[$key]->value ? "是" : "否";
                }
                $columns .= "<td class='text'>". $valueText ."</td>";
            }
            $tbodyRows .= "<tr>".  $columns ."</tr>";
        }

        $fullPageContent = $pageHead . $tableStart . $tbodyRows . <<<EOF
</tbody>
</table>
</body>
</html>
EOF;

        return
            response($fullPageContent)
                ->header("Cache-Control", "no-cache, no-store, must-revalidate")
                ->header("Content-type", "application/vnd.ms-excel")
                ->header("Content-Disposition", "attachment; filename=all-". $date . ".xls")
            ;
    }

    public function exportNotReported()
    {
        $date = $this->request->date;
        if (!$this->request->session()->get("export.authenticated", false) || !preg_match('/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}$/', $date)) {
            abort(403);
        }
        $notReportedUsers = DB::select("SELECT * FROM `users` WHERE id NOT IN (SELECT user_id FROM `user_health_reports` WHERE reported_date = ?)", [$date]);

        $pageHead = <<<EOF
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
EOF;
        $tableStart = <<<EOF
<table border="1">
<thead>
<tr>
    <th>姓名</th>
    <th>身份证号码</th>
</tr>
</thead>
<tbody>
EOF;

        $tbodyRows = "";
        foreach ($notReportedUsers as $user) {
            $columns = "<td class='text'>". $user->name ."</td><td class='text'>". $user->id_card_no ."</td>";
            $tbodyRows .= "<tr>".  $columns ."</tr>";
        }

        $fullPageContent = $pageHead . $tableStart . $tbodyRows . <<<EOF
</tbody>
</table>
</body>
</html>
EOF;

        return
            response($fullPageContent)
                ->header("Cache-Control", "no-cache, no-store, must-revalidate")
                ->header("Content-type", "application/vnd.ms-excel")
                ->header("Content-Disposition", "attachment; filename=not-reported-". $date . ".xls")
            ;
    }
}
