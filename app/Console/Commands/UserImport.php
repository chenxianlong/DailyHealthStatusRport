<?php

namespace App\Console\Commands;

use App\User;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Console\Command;

class UserImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:import {filePath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import user';

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
                $name = trim($cells[0]->getValue());
                $idCardNo = trim($cells[1]->getValue());
                $idCardNo = str_replace([
                    "\"",
                    "'",
                    "“",
                    "‘",
                ], "", $idCardNo);
                $type = $cells[2]->getValue();

                if ($name === "" || is_null($name) || $idCardNo === "" || is_null($idCardNo) || $type === "" || is_null($type)) {
                    $this->error($index . ": " . $name . " " . $idCardNo);
                    continue;
                }

                User::query()->firstOrCreate([
                    "id_card_no" => $idCardNo,
                ], ["name" => $name, "type" => $type]);
                // $this->info($name . " " . $idCardNo . " " . $type);
            }
        }
        return 0;
    }
}
