<?php

namespace App\Console\Commands;

use App\User;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Console\Command;

class ImportTeachers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teacher:import {filePath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import teachers';

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
                $jobNumber = (string) $cells[1]->getValue();
                $name = (string) $cells[2]->getValue();
                $idCardNo = (string) $cells[3]->getValue();
                $this->info($jobNumber . " " . $name . " " . $idCardNo);
                User::query()->where("user_id", $jobNumber)->update(["user_id" => null]);
                User::query()->updateOrCreate([
                    "id_card_no" => $idCardNo,
                ], [
                    "user_id" => $jobNumber,
                    "name" => $name,
                    "type" => 1,
                ]);
            }
        }
        return 0;
    }
}
