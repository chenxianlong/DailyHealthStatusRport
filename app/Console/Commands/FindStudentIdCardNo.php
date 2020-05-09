<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FindStudentIdCardNo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'student:find:id-card-no';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find students\' id card no';

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
        DB::insert("INSERT INTO `users` (id_card_no, name, department, type)
SELECT cs.id_card_no AS id_card_no, student2_find_id_card_nos.name AS name, cs.class AS department, 0 AS type
FROM `student2_find_id_card_nos`
         INNER JOIN ccdgut_students cs on student2_find_id_card_nos.no = cs.no
ON DUPLICATE KEY UPDATE users.id = users.id;
");
        foreach (DB::select("SELECT student2_find_id_card_nos.*
FROM `student2_find_id_card_nos`
         LEFT JOIN ccdgut_students cs on student2_find_id_card_nos.no = cs.no
WHERE cs.id IS NULL") as $student) {
            $this->error($student->no . " " . $student->name);
        }

        return 0;
    }
}
