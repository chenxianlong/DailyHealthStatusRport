<?php

namespace App\Console\Commands\WechatWork;

use App\WeChatWork\Account;
use App\WeChatWork\Application;
use Illuminate\Console\Command;

class DropApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ww:application:drop {appId} {agentId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop wechat work application';

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
        $account = Account::query()->where("app_id", $this->argument("appId"))->firstOrFail();
        Application::query()->where([
            "account" => $account->id,
            "agnet_id" => $this->argument("agnetId"),
        ])->delete();
        $this->info("application dropped.");
        return 0;
    }
}
