<?php

namespace App\Console\Commands;

use App\Student2FindIdCardNo;
use App\User;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Console\Command;

class ImportStudentClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'student:class:import {filePath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import student\'s class';

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
        $reader = ReaderEntityFactory::createReaderFromFile($filePath = $this->argument("filePath"));

        $reader->open($filePath);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {
                /**
                 * @var Cell[] $cells
                 */
                $cells = $row->getCells();
                $no = $cells[1]->getValue();
                $name = $cells[2]->getValue();
                $major = $cells[5]->getValue();
                $class = $cells[7]->getValue();
                $className = "2016级" . $major . $class . "班";

                $this->info($no . " " . $name . " " . $className);
                User::query()->where("user_id", $no)->update([
                    "department" => $className,
                ]);
            }
        }
        return 0;
    }
}
