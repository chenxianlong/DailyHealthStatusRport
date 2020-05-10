<?php

namespace App\Console\Commands;

use App\ExportUserIdWhiteList;
use App\User;
use Illuminate\Console\Command;

class ExportPermissionGrant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:grant {userNo} {allDepartment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant user export permission';

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
        ExportUserIdWhiteList::query()->updateOrCreate(["user_id" => $user->id, "all_department" => !!$this->argument("allDepartment")]);
        return 0;
    }
}
