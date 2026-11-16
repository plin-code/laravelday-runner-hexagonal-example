<?php

namespace App\Infrastructure\Filament\Resources\Contests\Pages;

use App\Infrastructure\Filament\Resources\Contests\ContestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContest extends CreateRecord
{
    protected static string $resource = ContestResource::class;
}
