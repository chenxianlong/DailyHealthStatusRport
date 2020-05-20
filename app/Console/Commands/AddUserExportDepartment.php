<?php

namespace App\Console\Commands;

use App\User;
use App\UserAllowExportDepartment;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddUserExportDepartment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:export:department:add {userId} {department}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add user export department';

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
        $value = $this->argument("userId");
        $column = "user_id";
        if (strlen($value) === 18) {
            $column = "id_card_no";
        }
        try {
            $user = User::query()->where($column, $value)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->error($this->argument("userId") . " not found.");
            return -1;
        }
        UserAllowExportDepartment::query()->firstOrCreate([
            "user_id" => $user->id,
            "department" => $this->argument("department"),
        ]);
        return 0;
    }
}
