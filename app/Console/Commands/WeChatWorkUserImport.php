<?php

namespace App\Console\Commands;

use App\User;
use App\WeChatWorkUsers;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Console\Command;

class WeChatWorkUserImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ww:user:import {filePath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import WeChatWork users';

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
                $userId = trim($cells[1]->getValue());
                $department = trim($cells[2]->getValue());
                $gender = trim($cells[3]->getValue());
                $phone = trim($cells[4]->getValue());

                WeChatWorkUsers::query()->firstOrCreate([
                    "user_id" => $userId,
                ], [
                    "name" => $name,
                    "department" => $department,
                    "gender" => $gender,
                    "phone" => $phone,
                ]);
                // $this->info($name . " " . $idCardNo . " " . $type);
            }
        }
        return 0;
    }
}
