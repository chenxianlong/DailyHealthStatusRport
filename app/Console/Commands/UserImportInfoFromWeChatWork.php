<?php

namespace App\Console\Commands;

use App\User;
use App\WeChatWorkUsers;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserImportInfoFromWeChatWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:ww-info:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users\' WeChat Work information';

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
        foreach (User::query()->where("type", 1)->whereNull("open_id")->whereNull("user_id")->get() as $user) {
            $matchUser = null;
            $weChatUserFindByName = WeChatWorkUsers::query()->where("name", $user->name)->get();
            if ($weChatUserFindByName->count() === 0) {
                $this->error($user->name . " not found.");
                continue;
            }
            if ($weChatUserFindByName->count() > 1) {
                $this->error($user->name . " duplicated.");
                continue;
            }
            $matchUser = $weChatUserFindByName->first();
            $user->update(["department" => $matchUser->department, "user_id" => $matchUser->user_id]);
        }
        return 0;
    }
}
