<?php

namespace App\Console\Commands\WechatWork;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PhoneStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ww:phone:statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WechatWork user phone statistics';

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
     * @return mixed
     */
    public function handle()
    {
        $ispMap = [
            "130" => "中国联通",
            "131" => "中国联通",
            "132" => "中国联通",
            "133" => "中国电信",
            "134" => "中国移动",
            "135" => "中国移动",
            "136" => "中国移动",
            "137" => "中国移动",
            "138" => "中国移动",
            "139" => "中国移动",
            "145" => "中国联通",
            "146" => "中国联通",
            "147" => "中国移动",
            "148" => "中国移动",
            "149" => "中国电信",
            "150" => "中国移动",
            "151" => "中国移动",
            "152" => "中国移动",
            "153" => "中国电信",
            "155" => "中国联通",
            "156" => "中国联通",
            "157" => "中国移动",
            "158" => "中国移动",
            "159" => "中国移动",
            "166" => "中国联通",
            "170" => "虚拟运营商",
            "171" => "中国联通",
            "172" => "中国移动",
            "173" => "中国电信",
            "174" => "中国电信",
            "175" => "中国联通",
            "176" => "中国联通",
            "177" => "中国电信",
            "178" => "中国移动",
            "180" => "中国电信",
            "181" => "中国电信",
            "182" => "中国移动",
            "183" => "中国移动",
            "184" => "中国移动",
            "185" => "中国联通",
            "186" => "中国联通",
            "187" => "中国移动",
            "188" => "中国移动",
            "189" => "中国电信",
            "198" => "中国移动",
            "199" => "中国电信",
        ];

        $counter = [];

        foreach (DB::select("SELECT COUNT(*) AS phone_prefix_count, SUBSTRING(`phone`, 1, 3) AS phone_prefix FROM `we_chat_work_users` WHERE department NOT LIKE '城市学院/学生/%' AND department NOT LIKE '城市学院/校友/%' GROUP BY phone_prefix") as $row) {
            $prefix = $row->phone_prefix;
            if (array_key_exists($prefix, $ispMap)) {
                $key = $ispMap[$prefix];
            } else {
                $key = "未知";
            }
            if (!array_key_exists($key, $counter)) {
                $counter[$key] = 0;
            }
            $counter[$key] += $row->phone_prefix_count;
        }

        print_r($counter);

        return 0;
    }
}
