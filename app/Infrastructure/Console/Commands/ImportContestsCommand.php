<?php

namespace App\Infrastructure\Console\Commands;

use App\Application\Actions\Contests\ImportContestsFromCsvAction;
use App\Application\Exceptions\InvalidContestsCsvException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('contests:import {path? : CSV file to import, database/data/contests.csv when omitted}')]
#[Description('Import the contests from a CSV file')]
class ImportContestsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ImportContestsFromCsvAction $importContests): int
    {
        $path = $this->argument('path') ?? database_path('data/contests.csv');

        try {
            $imported = $importContests->execute($path);
        } catch (InvalidContestsCsvException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Contests imported: {$imported}.");

        return self::SUCCESS;
    }
}
