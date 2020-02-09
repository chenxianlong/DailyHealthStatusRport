<?php

namespace App\Console\Commands\WechatWork;

use App\WeChatWork\Account;
use App\WeChatWork\Application;
use Illuminate\Console\Command;

class SyncApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ww:application:sync {appId} {name} {agentId} {secret}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update or create wechat work application';

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
        Application::query()->updateOrCreate([
            "account_id" => $account->id,
            "agent_id" => $this->argument("agentId"),
        ], [
            "secret" => $this->argument("secret"),
            "name" => $this->argument("name"),
        ]);
        $this->info("application synced.");
        return 0;
    }
}
