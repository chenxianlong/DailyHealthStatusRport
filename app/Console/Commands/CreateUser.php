<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {idCardNo} {name} {department} {type} {userId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user';

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
        User::query()->firstOrCreate([
            "id_card_no" => $this->argument("idCardNo"),
        ], [
            "name" => $this->argument("name"),
            "department" => $this->argument("department"),
            "type" => $this->argument("type"),
            "user_id" => $this->argument("userId"),
        ]);
        return 0;
    }
}
