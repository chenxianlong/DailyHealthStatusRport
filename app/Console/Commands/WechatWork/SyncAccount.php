<?php

namespace App\Console\Commands\WechatWork;

use App\WeChatWork\Account;
use Illuminate\Console\Command;

class SyncAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ww:account:sync {appId} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update wechat work account';

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
        Account::query()->updateOrCreate([
            "app_id" => $this->argument("appId"),
        ], [
            "name" => $this->argument("name"),
        ]);
        $this->info("account synced.");
        return 0;
    }
}
