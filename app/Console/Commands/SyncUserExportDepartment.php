<?php

namespace App\Console\Commands;

use App\User;
use App\UserAllowExportDepartment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncUserExportDepartment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:export:department:sync {userId} {departments?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync user export department';

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
        $user = User::query()->where("user_id", $this->argument("userId"))->firstOrFail();
        $departmentList = explode(";", $this->argument("departments"));
        $values = [];
        foreach ($departmentList as $department) {
            if (empty($department)) {
                continue;
            }
            $values[] = [
                "user_id" => $user->id,
                "department" => $department,
            ];
        }
        DB::transaction(function () use ($user, &$values) {
            UserAllowExportDepartment::query()->where("user_id", $user->id)->delete();
            UserAllowExportDepartment::query()->insert($values);
        });
        return 0;
    }
}
