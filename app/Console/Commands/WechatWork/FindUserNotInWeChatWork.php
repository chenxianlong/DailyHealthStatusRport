<?php

namespace App\Console\Commands\WechatWork;

use App\User;
use App\WeChatWorkUsers;
use Illuminate\Console\Command;

class FindUserNotInWeChatWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ww:user:not-in:find {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find user not in wechat work';

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
        foreach (User::query()->where("type", $this->argument("type"))->get() as $user) {
            if (is_null(WeChatWorkUsers::query()->where("id_card_no", $user->id_card_no)->orWhere("user_id", $user->user_id)->first())) {
                $this->info($user->name . " " . $user->id_card_no . " " . $user->department . " " . $user->user_id);
            }
        }
        return 0;
    }
}
