<?php

namespace App\Console\Commands;

use App\User;
use App\UserAllowExportSelfDepartment;
use Illuminate\Console\Command;

class GrantUserExportSelfDepartment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:export:self:department:grant {userId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant user\'s permission to export self department';

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
        UserAllowExportSelfDepartment::query()->firstOrCreate([
            "user_id" => User::query()->where("user_id", $this->argument("userId"))->firstOrFail()->id,
        ]);
        return 0;
    }
}
