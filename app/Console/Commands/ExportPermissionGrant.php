<?php

namespace App\Console\Commands;

use App\ExportUserIdWhiteList;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportPermissionGrant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:grant {userNo} {types?}';

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
        $value = $this->argument("userNo");
        $column = "user_id";
        if (strlen($value) === 18) {
            $column = "id_card_no";
        }
        $user = User::query()->where($column, $value)->firstOrFail();
        $values = [];
        foreach (explode(",", $this->argument("types")) as $type) {
            if ($type === "") {
                continue;
            }
            $values[] = [
                "user_id" => $user->id,
                "type" => $type,
            ];
        }
        DB::transaction(function () use ($user, &$values) {
            ExportUserIdWhiteList::query()->where("user_id", $user->id)->delete();
            ExportUserIdWhiteList::query()->insert($values);
        });
        return 0;
    }
}
