<?php

namespace App\Application\Actions\Contests;

use App\Application\Actions\BaseAction;
use App\Application\Exceptions\InvalidContestsCsvException;
use App\Domain\Contests\Models\Contest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ImportContestsFromCsvAction extends BaseAction
{
    /** @var list<string> */
    private const array COLUMNS = ['name', 'date', 'available_places', 'registrations_open'];

    /**
     * Import the contests of a CSV file and return how many rows were read.
     *
     * The import can be repeated: a contest is identified by name and date, so a second
     * run updates the rows instead of duplicating them. One invalid row rolls everything back.
     *
     * @throws InvalidContestsCsvException
     */
    public function execute(string $path): int
    {
        $rows = $this->read($path);

        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                Contest::query()->updateOrCreate(
                    ['name' => $row['name'], 'date' => $row['date']],
                    [
                        'available_places' => $row['available_places'],
                        'registrations_open' => $row['registrations_open'],
                    ],
                );
            }
        });

        $this->log('Contests imported from CSV', ['path' => $path, 'rows' => count($rows)]);

        return count($rows);
    }

    /**
     * @return list<array{name: string, date: string, available_places: int, registrations_open: bool}>
     */
    private function read(string $path): array
    {
        $handle = is_file($path) && is_readable($path) ? fopen($path, 'r') : false;

        if ($handle === false) {
            throw InvalidContestsCsvException::unreadable($path);
        }

        try {
            $header = fgetcsv($handle, escape: '');

            if (! is_array($header) || $this->normalizeHeader($header) !== self::COLUMNS) {
                throw InvalidContestsCsvException::missingHeader(self::COLUMNS);
            }

            $rows = [];
            $line = 1;

            while (($values = fgetcsv($handle, escape: '')) !== false) {
                $line++;

                if ($values === [null]) {
                    continue;
                }

                $rows[] = $this->parseRow($values, $line);
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param  array<int, string|null>  $header
     * @return list<string>
     */
    private function normalizeHeader(array $header): array
    {
        return array_map(
            fn (?string $column): string => strtolower(trim((string) $column, " \t\n\r\0\x0B\u{FEFF}")),
            array_values($header),
        );
    }

    /**
     * @param  array<int, string|null>  $values
     * @return array{name: string, date: string, available_places: int, registrations_open: bool}
     */
    private function parseRow(array $values, int $line): array
    {
        if (count($values) !== count(self::COLUMNS)) {
            throw InvalidContestsCsvException::invalidRow($line, 'expected '.count(self::COLUMNS).' columns.');
        }

        $row = array_combine(self::COLUMNS, array_map(fn (?string $value): string => trim((string) $value), $values));

        $validator = Validator::make($row, [
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
            'available_places' => ['required', 'integer', 'min:0'],
            'registrations_open' => ['required', 'in:0,1,true,false'],
        ]);

        if ($validator->fails()) {
            throw InvalidContestsCsvException::invalidRow($line, $validator->errors()->first());
        }

        return [
            'name' => $row['name'],
            'date' => $row['date'],
            'available_places' => (int) $row['available_places'],
            'registrations_open' => filter_var($row['registrations_open'], FILTER_VALIDATE_BOOLEAN),
        ];
    }
}
