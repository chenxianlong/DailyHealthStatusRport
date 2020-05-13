<?php

namespace App\Http\Controllers;

use App\Constants\AvailableAttributes;
use App\Constants\UserType;
use App\ExportUserIdWhiteList;
use App\User;
use App\UserAllowExportDepartment;
use App\UserDailyHealthStatus;
use App\UserHealthCard;
use App\UserHealthReports;
use App\Utils\Views;
use App\WeChatWork\SessionUtils;
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

    public function status(SessionUtils $sessionUtils)
    {
        $this->userExportPermissions($sessionUtils, $allowExportTeachers, $allowExportStudents);
        return Views::successAPIResponse([
            "authenticated" => true,
            "availableDates" => UserDailyHealthStatus::query()->groupBy("reported_date")->pluck("reported_date", "reported_date"),
            "availableClasses" => User::query()->select(["department"])->where("type", UserType::STUDENT)->groupBy("department")->pluck("department"),
            "allowExportTeachers" => $allowExportTeachers,
            "allowExportStudents" => $allowExportStudents,
        ]);
    }

    public function exportAll(SessionUtils $sessionUtils)
    {
        $this->validate($this->request, [
            "type" => [
                "required",
                Rule::in([0, 1]),
            ],
            "beginAt" => [
                "nullable",
                "date_format:Y-m-d",
            ],
            "endAt" => [
                "nullable",
                "date_format:Y-m-d",
            ],
        ]);

        $this->userExportPermissions($sessionUtils, $allowExportTeachers, $allowExportStudents);
        if ($allowExportTeachers !== 2 && $this->request->type == 1) {
            abort(403, "无导出教职工数据权限");
        }
        if ($allowExportStudents !== 2 && $this->request->type == 0) {
            abort(403, "无导出学生数据权限");
        }

        $availableDatesQueryBuilder = UserDailyHealthStatus::query();
        // if ($this->request->type != -1) {
        $availableDatesQueryBuilder->join("users", "user_daily_health_statuses.user_id", "=", "users.id")->where("users.type", $this->request->type);
        // }

        if ($this->request->startAt) {
            $availableDatesQueryBuilder->where("user_daily_health_statuses.reported_date", ">=", $this->request->startAt);
        }
        if ($this->request->endAt) {
            $availableDatesQueryBuilder->where("user_daily_health_statuses.reported_date", "<=", $this->request->endAt);
        }

        $userAllowExportDepartmentList = UserAllowExportDepartment::query()->where("user_id", $sessionUtils->getUser()->id)->pluck("department")->toArray();
        if (count($userAllowExportDepartmentList)) {
            $availableDatesQueryBuilder->where(function ($builder) use (&$userAllowExportDepartmentList) {
                foreach ($userAllowExportDepartmentList as $userAllowExportDepartment) {
                    $builder->orWhere("users.department", "LIKE", $userAllowExportDepartment . "%");
                }
            });
        }

        $availableDates = $availableDatesQueryBuilder->groupBy("reported_date")->orderBy("reported_date")->pluck("reported_date")->toArray();

        $dateCount = count($availableDates);

        $userDailyHealthStatusesQueryBuilder = UserDailyHealthStatus::query()->join("users", "user_daily_health_statuses.user_id", "=", "users.id");

        if ($this->request->startAt) {
            $userDailyHealthStatusesQueryBuilder->where("user_daily_health_statuses.reported_date", ">=", $this->request->startAt);
        }
        if ($this->request->endAt) {
            $userDailyHealthStatusesQueryBuilder->where("user_daily_health_statuses.reported_date", "<=", $this->request->endAt);
        }

        if (count($userAllowExportDepartmentList)) {
            $userDailyHealthStatusesQueryBuilder->where(function ($builder) use (&$userAllowExportDepartmentList) {
                foreach ($userAllowExportDepartmentList as $userAllowExportDepartment) {
                    $builder->orWhere("users.department", "LIKE", $userAllowExportDepartment . "%");
                }
            });
        }
        $userDailyHealthStatusesQueryBuilder->where("users.type", $this->request->type);
        /*
        if ($this->request->type === 0) {
            $selectedClasses = $this->request->selectedClasses;
            if (count($selectedClasses)) {
                $userDailyHealthStatusesQueryBuilder->whereIn("users.department", $selectedClasses);
            }
        }
        */
        $userDailyHealthStatuses = $userDailyHealthStatusesQueryBuilder->select(["user_daily_health_statuses.*", "users.name", "users.department", "users.type", "users.id_card_no"])->orderBy("users.id")->orderBy("users.department")->get()->groupBy("user_id");

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
EOF
        );

        if ($this->request->type === 1) {
            fwrite($fp, <<<EOF
<tr>
    <th rowspan="2">姓名</th>
    <th rowspan="2">部门</th>
    <th rowspan="2">联系电话</th>
    <th rowspan="2">现居住地址</th>
EOF
            );
            foreach ($availableDates as $date) {
                fwrite($fp, "<th class='text' colspan='4'>" . substr($date, 0, 10) . "</th>");
            }
            fwrite($fp, "</tr><tr>
");
            fwrite($fp, str_repeat("
    <th class='text'>本人身体健康状况</th>
    <th class='text'>同住家庭成员身体状况</th>
    <th class='text'>家庭成员是否接触过确诊病例、疑似病例或无症状感染者</th>
    <th class='text'>当日是否在校上班</th>
", $dateCount));
            fwrite($fp, <<<EOF
</tr>
EOF
            );
        } else {
            fwrite($fp, <<<EOF
<tr>
    <th rowspan="2">姓名</th>
    <th rowspan="2">班级</th>
    <th rowspan="2">联系电话</th>
    <th rowspan="2">现居住地址</th>
    <th rowspan="2">宿舍床位号</th>
    <th rowspan="2">曾前往疫情防控重点地区</th>
    <th rowspan="2">前往时间</th>
    <th rowspan="2">离开时间</th>
    <th rowspan="2">返莞时间</th>
    <th rowspan="2">曾接触过疫情防疫重点地区高危人员</th>
    <th rowspan="2">接触时间</th>
EOF
            );
            foreach ($availableDates as $date) {
                fwrite($fp, "<th class='text' colspan='2'>" . substr($date, 0, 10) . "</th>");
            }
            fwrite($fp, "</tr><tr>
");
            fwrite($fp, str_repeat("
    <th class='text'>本人身体健康状况</th>
    <th class='text'>同住家庭成员身体状况</th>
", $dateCount));
            fwrite($fp, <<<EOF
</tr>
EOF
            );
        }

        /*
        foreach ($availableDates as $date) {
            fwrite($fp, "<td>" . substr($date, 0, 10) . "</td>");
        }
        foreach ($availableDates as $date) {
            fwrite($fp, "<td>" . substr($date, 0, 10) . "</td>");
        }
        */

        fwrite($fp, <<<EOF
</thead>
<tbody>
EOF
        );

        if ($this->request->type === 1) {
            foreach ($userDailyHealthStatuses as $userId => $statuses) {
                /**
                 * @var Collection $statuses
                 */
                $statusesKeyByDate = $statuses->keyBy("reported_date");
                $firstStatus = $statuses->first();
                $userHealthCard = UserHealthCard::query()->find($userId);
                $row = "<tr>";
                $row .= "<td class='text'>" . htmlentities($firstStatus->name) . "</td>";
                $row .= "<td class='text'>" . htmlentities($firstStatus->department) . "</td>";
                if ($userHealthCard) {
                    $row .= "<td class='text'>" . $userHealthCard->phone . "</td>";
                    $row .= "<td class='text'>" . $userHealthCard->address . "</td>";
                    /*
                    $row .= "<td class='text'>" . ($userHealthCard->in_key_places_from ? "是" : "否") . "</td>";
                    $row .= "<td class='text'>" . $userHealthCard->in_key_places_from . "</td>";
                    $row .= "<td class='text'>" . $userHealthCard->in_key_places_to . "</td>";
                    $row .= "<td class='text'>" . $userHealthCard->back_to_dongguan_at . "</td>";
                    $row .= "<td class='text'>" . ($userHealthCard->touched_high_risk_people_at ? "是" : "否") . "</td>";
                    $row .= "<td class='text'>" . $userHealthCard->touched_high_risk_people_at . "</td>";
                    */
                } else {
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    /*
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    */
                }

                $columns = "";
                foreach ($availableDates as $date) {
                    if ($statusesKeyByDate->has($date)) {
                        $status = $statusesKeyByDate->get($date);
                        $extra = json_decode($status->extra);
                        if ($status->self_status) {
                            $selfStatus = "异常：" . htmlentities($status->self_status_details);
                        } else {
                            $selfStatus = "正常";
                        }
                        $columns .= "<td class='text'>" . $selfStatus . "</td>";
                        if ($status->family_status) {
                            $familyStatus = "异常：" . htmlentities($status->family_status_details);
                        } else {
                            $familyStatus = "正常";
                        }
                        $columns .= "<td class='text'>" . $familyStatus . "</td>";
                        if ($extra) {
                            if (property_exists($extra, "today_touch_risk_people")) {
                                if ($extra->today_touce_risk_people) {
                                    $todayTouchRiskPeople = "是";
                                } else {
                                    $todayTouchRiskPeople = "否";
                                }
                            } else {
                                $todayTouchRiskPeople = "";
                            }
                            $columns .= "<td class='text'>" . $todayTouchRiskPeople . "</td>";
                            if (property_exists($extra, "today_work_in_school")) {
                                if ($extra->today_work_in_school) {
                                    $todayWorkInSchool = "是";
                                } else {
                                    $todayWorkInSchool = "否";
                                }
                            } else {
                                $todayWorkInSchool = "";
                            }
                            $columns .= "<td class='text'>" . $todayWorkInSchool . "</td>";
                        } else {
                            $columns .= "<td class='text'></td>";
                            $columns .= "<td class='text'></td>";
                        }
                    } else {
                        $columns .= "<td class='text'></td>";
                        $columns .= "<td class='text'></td>";
                        $columns .= "<td class='text'></td>";
                        $columns .= "<td class='text'></td>";
                    }
                }
                fwrite($fp, $row);
                fwrite($fp, $columns);
                fwrite($fp, "</tr>");
            }
        } else {
            foreach ($userDailyHealthStatuses as $userId => $statuses) {
                /**
                 * @var Collection $statuses
                 */
                $statusesKeyByDate = $statuses->keyBy("reported_date");
                $firstStatus = $statuses->first();
                $userHealthCard = UserHealthCard::query()->find($userId);
                $row = "<tr>";
                $row .= "<td class='text'>" . htmlentities($firstStatus->name) . "</td>";
                $row .= "<td class='text'>" . htmlentities($firstStatus->department) . "</td>";
                if ($userHealthCard) {
                    $row .= "<td class='text'>" . $userHealthCard->phone . "</td>";
                    $row .= "<td class='text'>" . $userHealthCard->address . "</td>";
                    $row .= "<td class='text'>" . $userHealthCard->dorm_room . "</td>";
                    $row .= "<td class='text'>" . ($userHealthCard->in_key_places_from ? "是" : "否") . "</td>";
                    $row .= "<td class='text'>" . $userHealthCard->in_key_places_from . "</td>";
                    $row .= "<td class='text'>" . $userHealthCard->in_key_places_to . "</td>";
                    $row .= "<td class='text'>" . $userHealthCard->back_to_dongguan_at . "</td>";
                    $row .= "<td class='text'>" . ($userHealthCard->touched_high_risk_people_at ? "是" : "否") . "</td>";
                    $row .= "<td class='text'>" . $userHealthCard->touched_high_risk_people_at . "</td>";
                } else {
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                    $row .= "<td class='text'></td>";
                }

                $columns = "";
                foreach ($availableDates as $date) {
                    if ($statusesKeyByDate->has($date)) {
                        $status = $statusesKeyByDate->get($date);
                        if ($status->self_status) {
                            $selfStatus = "异常：" . htmlentities($status->self_status_details);
                        } else {
                            $selfStatus = "正常";
                        }
                        $columns .= "<td class='text'>" . $selfStatus . "</td>";
                        if ($status->family_status) {
                            $familyStatus = "异常：" . htmlentities($status->family_status_details);
                        } else {
                            $familyStatus = "正常";
                        }
                        $columns .= "<td class='text'>" . $familyStatus . "</td>";
                    } else {
                        $columns .= "<td class='text'></td>";
                        $columns .= "<td class='text'></td>";
                    }
                }
                fwrite($fp, $row);
                fwrite($fp, $columns);
                fwrite($fp, "</tr>");
            }
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
            "userId" => $sessionUtils->getUser()->id,
            "salt" => $salt = base64_encode(random_bytes(8)),
            "signature" => sha1($filename . $expireAt . $sessionUtils->getUser()->id . $salt . env("HR_EXPORT_PASSWORD")),
        ]);
        response($fullPageContent)
            ->header("Cache-Control", "no-cache, no-store, must-revalidate")
            ->header("Content-type", "application/vnd.ms-excel")
            ->header("Content-Disposition", "attachment; filename=all-" . time() . ".xls");
    }

    public function exportNotReported(SessionUtils $sessionUtils)
    {
        $this->validate($this->request, [
            "date" => [
                "required",
                "date_format:Y-m-d",
            ],
            "type" => [
                "required",
                Rule::in([0, 1]),
            ],
        ]);

        $this->userExportPermissions($sessionUtils, $allowExportTeachers, $allowExportStudents);
        if ($allowExportTeachers === 0 && $this->request->type == 1) {
            abort(403, "无导出教师数据权限");
        }
        if ($allowExportStudents === 0 && $this->request->type == 0) {
            abort(403, "无导出学生数据权限");
        }

        $date = $this->request->date;

        $departmentLikeWhere = "";
        $departmentLikeValues = [];
        $userAllowExportDepartmentList = UserAllowExportDepartment::query()->where("user_id", $sessionUtils->getUser()->id)->pluck("department")->toArray();
        if ($userAllowExportDepartmentListCount = count($userAllowExportDepartmentList)) {
            $departmentLikeWhere = str_repeat(" department LIKE ? OR", $userAllowExportDepartmentListCount);
            $departmentLikeWhere = " AND (" . substr($departmentLikeWhere, 0, strlen($departmentLikeWhere) - 2) . ")";
            foreach ($userAllowExportDepartmentList as $department) {
                $departmentLikeValues[] = $department . "%";
            }
        }
        $selectedClassesIn = "";
        $selectedClassesValues = [];
        if ($this->request->type == 0) {
            $selectedClassesValues = $this->request->selectedClasses;
            if ($selectedClassesCount = count($selectedClassesValues)) {
                $valueBinding = str_repeat("? ,", $selectedClassesCount);
                $valueBinding = substr($valueBinding, 0, strlen($valueBinding) - 1);
                $selectedClassesIn = " AND department IN (" . $valueBinding . ")";
            }
        }

        $values2Bind = array_merge([$date, $this->request->type], $departmentLikeValues, $selectedClassesValues);

        /*
        if ($this->request->type != -1) {
        */
        $query = "SELECT * FROM `users` WHERE id NOT IN (SELECT user_id FROM `user_daily_health_statuses` WHERE reported_date = ?) AND type = ?" . $departmentLikeWhere . $selectedClassesIn;
        $notReportedUsers = DB::select($query, $values2Bind);
        /*
        } else {
            $notReportedUsers = DB::select("SELECT * FROM `users` WHERE id NOT IN (SELECT user_id FROM `user_daily_health_statuses` WHERE reported_date = ?)", [$date]);
        }
        */

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
    <th>部门/班级</th>
</tr>
</thead>
<tbody>
EOF
        );

        foreach ($notReportedUsers as $user) {
            fwrite($fp, "<tr>");
            fwrite($fp, "<td class='text'>" . $user->name . "</td><td class='text'>" . $user->department . "</td>");
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
            "userId" => $sessionUtils->getUser()->id,
            "salt" => $salt = base64_encode(random_bytes(8)),
            "signature" => sha1($filename . $expireAt . $sessionUtils->getUser()->id . $salt . env("HR_EXPORT_PASSWORD")),
        ]);
        return
            response($fullPageContent)
                ->header("Cache-Control", "no-cache, no-store, must-revalidate")
                ->header("Content-type", "application/vnd.ms-excel")
                ->header("Content-Disposition", "attachment; filename=not-reported-" . $date . ".xls");
    }

    public function download()
    {
        if (!hash_equals(sha1($this->request->filename . $this->request->expireAt . $this->request->userId . $this->request->salt . env("HR_EXPORT_PASSWORD")), $this->request->signature)) {
            abort(403, "参数验签不通过");
        }
        if (intval($this->request->expireAt) < time()) {
            abort(403, "链接已过期");
        }

        return response()->download($this->storeDirectory . $this->request->filename);
    }

    private function userExportPermissions(SessionUtils $sessionUtils, &$allowExportTeachers, &$allowExportStudents)
    {
        $user = $sessionUtils->getUser();
        $exportPermission = ExportUserIdWhiteList::query()->where("user_id", $user->id)->get()->pluck("type", "type")->toArray();
        $userAllowExportDepartmentCount = UserAllowExportDepartment::query()->where("user_id", $user->id)->count();
        if (array_key_exists(UserType::TEACHER, $exportPermission)) {
            $allowExportTeachers = 2;
        } else if ($userAllowExportDepartmentCount) {
            $allowExportTeachers = 1;
        } else {
            $allowExportTeachers = 0;
        }

        if (array_key_exists(UserType::STUDENT, $exportPermission) || $userAllowExportDepartmentCount) {
            $allowExportStudents = 2;
        } else if (strlen($sessionUtils->getUserId()) === 8) {
            $allowExportStudents = 1;
        } else {
            $allowExportStudents = 0;
        }
    }
}
