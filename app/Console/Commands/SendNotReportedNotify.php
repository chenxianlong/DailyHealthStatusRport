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
    protected $signature = 'nr:notify:send {type}';

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
            try {
                $serverAPI->sendMessage("text", ["text" => ["content" => '尊敬的' . $user->name . '，您好，根据疫情防控有关文件的要求，从2月11日开始，我校每位教工必须每天如实填报“健康卡“，返校前至少要提供十四天的健康数据。系统检测到您今天还没有填报，请选择下面的方式填报：
1，点击本链接: http://2020.smart-ccdgut.com/healthStatus/daily 直接填报。
2，点击下方“健康卡”菜单填报。
因为健康卡需要至少十四天的数据，请记得每天都要在此填报，如有问题请及时与本部门秘书联系，感谢你的支持与配合']], $user->user_id);
                $this->info("#" . $user->id . " " . $user->name . " sent.");
            } catch (\Exception $e) {
                Common::logException($e);
                $this->error("#" . $user->id . " " . $user->name . ": " . $e->getMessage());
            }
        }
        return 0;
    }
}
