<?php

namespace App\Infrastructure\Filament\Resources\Contests\Pages;

use App\Infrastructure\Filament\Resources\Contests\ContestResource;
use Filament\Resources\Pages\EditRecord;

class EditContest extends EditRecord
{
    protected static string $resource = ContestResource::class;
}
