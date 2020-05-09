<?php

namespace App\Console\Commands;

use App\Student2FindIdCardNo;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Console\Command;

class ImportStudent2FindIdCardNo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'student:find:id-card-no:import {filePath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import students need to find id card no';

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
                $no = $cells[1];
                $name = $cells[2];

                Student2FindIdCardNo::query()->insert([
                    "no" => $no,
                    "name" => $name,
                ]);
            }
        }
        return 0;
    }
}
