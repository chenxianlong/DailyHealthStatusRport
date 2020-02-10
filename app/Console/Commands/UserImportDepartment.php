<?php

namespace App\Console\Commands;

use App\User;
use App\WeChatWorkUsers;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserImportDepartment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:department:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users\' departments';

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
        foreach (User::query()->where("type", 1)->get() as $user) {
            try {
                $weChatUser = WeChatWorkUsers::query()->where("name", $user->name)->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $this->error($user->name . " not found.");
            }
            $user->update(["department" => $weChatUser->department]);
        }
        return 0;
    }
}
