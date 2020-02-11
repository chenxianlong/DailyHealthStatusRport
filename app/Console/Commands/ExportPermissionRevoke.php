<?php

namespace App\Console\Commands;

use App\ExportUserIdWhiteList;
use App\User;
use Illuminate\Console\Command;

class ExportPermissionRevoke extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:revoke {userNo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke user export permission.';

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
        $user = User::query()->where("user_id", $this->argument("userNo"))->firstOrFail();
        ExportUserIdWhiteList::query()->where("user_id", $user->id)->delete();
        return 0;
    }
}
