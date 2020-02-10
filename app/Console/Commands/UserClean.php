<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all users';

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
        DB::delete("DELETE FROM `users`");
        DB::statement("ALTER TABLE `users` AUTO_INCREMENT = 1");
        return 0;
    }
}
