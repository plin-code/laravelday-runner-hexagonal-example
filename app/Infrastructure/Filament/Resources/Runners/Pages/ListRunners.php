<?php

namespace App\Infrastructure\Filament\Resources\Runners\Pages;

use App\Infrastructure\Filament\Resources\Runners\RunnerResource;
use Filament\Resources\Pages\ListRecords;

class ListRunners extends ListRecords
{
    protected static string $resource = RunnerResource::class;
}
