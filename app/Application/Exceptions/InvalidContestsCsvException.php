<?php

namespace App\Application\Exceptions;

use RuntimeException;

class InvalidContestsCsvException extends RuntimeException
{
    public static function unreadable(string $path): self
    {
        return new self("The file [{$path}] does not exist or cannot be read.");
    }

    /**
     * @param  list<string>  $expected
     */
    public static function missingHeader(array $expected): self
    {
        return new self('The first line must be the header with the columns: '.implode(', ', $expected).'.');
    }

    public static function invalidRow(int $line, string $reason): self
    {
        return new self("Line {$line} is not valid: {$reason}");
    }
}
