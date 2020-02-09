<?php

namespace App\Console\Commands\WechatWork;

use App\WeChatWork\Account;
use Illuminate\Console\Command;

class DropAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ww:account:drop {appId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop wechat work account';

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
        Account::query()->where("app_id", $this->argument("appId"))->delete();
        $this->info("account dropped.");
        return 0;
    }
}
