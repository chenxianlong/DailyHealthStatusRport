<?php

namespace App\Console\Commands;

use App\Utils\Common;
use App\WeChatWork\Contract\ServerAPIFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendNotReportedNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nr:notify:send {type} {--first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send not reported notify.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param ServerAPIFactory $serverAPIFactory
     * @return mixed
     */
    public function handle(ServerAPIFactory $serverAPIFactory)
    {
        $serverAPI = $serverAPIFactory->make(env("HR_APPLICATION_ID"));
        $notReportedUsers = DB::select("SELECT * FROM `users` WHERE id NOT IN (SELECT user_id FROM `user_daily_health_statuses` WHERE reported_date = ?) AND user_id IS NOT NULL AND type = ?", [date("Y-m-d"), $this->argument("type")]);
        foreach ($notReportedUsers as $user) {
            $retryCounter = 0;
            while (true) {
                if ($retryCounter > 2) {
                    break;
                }
                try {
                    if ($this->option("first")) {
                        $message = $user->name . " 您好，根据疫情防控有关文件的要求，返校前要提供十四天以上的健康数据，请您每天如实填报“健康卡\"。请选择下面的方式填报：
1，点击本链接: https://2020.smart-ccdgut.com/healthStatus/daily 直接填报。
2，点击下方“健康卡”菜单填报。";
                        /*
                        $message = '尊敬的'. $user->name .' 您好，根据疫情防控有关文件的要求，从2月11日开始，我校每位教工必须每天如实填报“健康卡“，返校前至少要提供十四天的健康数据，每天只能当天填报一次。请选择下面的方式填报：
    1，点击本链接: https://2020.smart-ccdgut.com/healthStatus/daily 直接填报。
    2，点击下方“健康卡”菜单填报。
    如有问题请及时与本部门秘书联系，感谢你的支持与配合。';
                        */
                    } else {
                        $message = $user->name . " 您好，根据疫情防控有关文件的要求，返校前要提供十四天以上的健康数据，请您每天如实填报“健康卡\"。系统检测到您今天还没有填报，请选择下面的方式填报：
1，点击本链接: https://2020.smart-ccdgut.com/healthStatus/daily 直接填报。
2，点击下方“健康卡”菜单填报。";
                        /*
                        $message = '尊敬的' . $user->name . '，您好，根据疫情防控有关文件的要求，从2月11日开始，我校每位教工必须每天如实填报“健康卡“，返校前至少要提供十四天的健康数据。
    系统检测到您今天还没有填报，请选择下面的方式填报：
    1，点击本链接: https://2020.smart-ccdgut.com/healthStatus/daily 直接填报。
    2，点击下方“健康卡”菜单填报。
    因为健康卡需要至少十四天的数据，请记得每天都要在此填报，如有问题请及时与本部门秘书联系，感谢你的支持与配合';
                        */
                    }
                    $serverAPI->sendMessage("text", ["text" => ["content" => $message]], $user->user_id);
                    $this->info("#" . $user->id . " " . $user->name . " sent.");
                    break;
                } catch (\Exception $e) {
                    ++$retryCounter;
                    Common::logException($e);
                    $this->error("#" . $user->id . " " . $user->name . ": " . $e->getMessage());
                }
            }
        }
        return 0;
    }
}
