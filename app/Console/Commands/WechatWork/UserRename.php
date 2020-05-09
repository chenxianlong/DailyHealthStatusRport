<?php

namespace App\Console\Commands\WechatWork;

use App\Utils\Common;
use App\WeChatWork\Contract\ServerAPIFactory;
use App\WeChatWorkUsers;
use Illuminate\Console\Command;

class UserRename extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ww:user:rename';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rename WeChat Work users.';

    /**
     * @var ServerAPIFactory $serverAPIFactory
     */
    private $serverAPIFactory;

    /**
     * Create a new command instance.
     *
     * @param ServerAPIFactory $serverAPIFactory
     * @return void
     */
    public function __construct(ServerAPIFactory $serverAPIFactory)
    {
        parent::__construct();
        $this->serverAPIFactory = $serverAPIFactory;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $serverAPI = $this->serverAPIFactory->make(2);
        $total = WeChatWorkUsers::query()->where("department", "LIKE" , "城市学院/学生/%")->count();
        $counter = 1;
        foreach (WeChatWorkUsers::query()->where("department", "LIKE" , "城市学院/学生/%")->get() as $item) {
            while (true) {
                try {
                    $this->info($counter . "/" . $total . " #" . $item->id . " " . $item->name . " processing...");
                    $newName = sprintf("%s-%s", $item->name, $item->user_id);
                    $this->info($counter . "/" . $total . " #" . $item->id . " " . $item->name . " new name: " . $newName . ".");
                    $serverAPI->updateUser($item->user_id, [
                        "name" => $newName,
                    ]);
                    $this->info($counter . "/" . $total . " #" . $item->id . " " . $item->name . " processed.");
                    break;
                } catch (\Throwable $throwable) {
                    $this->error($throwable->getMessage());
                    Common::logException($throwable);
                    sleep(3);
                }
            }
            ++$counter;
        }
        return 0;
    }
}
