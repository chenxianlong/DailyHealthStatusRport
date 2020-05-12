<?php

namespace App\Console\Commands;

use App\User;
use App\WeChatWorkUsers;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SyncUserDepartmentFromWeChatWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:department:from-ww:sync {userType}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users\' departments from WeChatWork users';

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
        foreach (User::query()->where("type", $this->argument("userType"))->get() as $user) {
            try {
                $weChatWorkUser = WeChatWorkUsers::query()->where("id_card_no", $user->id_card_no)->orWhere("user_id", $user->user_id)->firstOrFail();
                $user->update([
                    "department" => $weChatWorkUser->department,
                ]);
            } catch (ModelNotFoundException $modelNotFoundException) {
                $this->error($user->name . " " . $user->id_card_no . " " . $user->user_id);
            }
        }
        return 0;
    }
}
