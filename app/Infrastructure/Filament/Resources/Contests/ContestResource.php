<?php

namespace App\Infrastructure\Filament\Resources\Contests;

use App\Domain\Contests\Models\Contest;
use App\Infrastructure\Filament\Resources\Contests\Pages\CreateContest;
use App\Infrastructure\Filament\Resources\Contests\Pages\EditContest;
use App\Infrastructure\Filament\Resources\Contests\Pages\ListContests;
use App\Infrastructure\Filament\Resources\Contests\Schemas\ContestForm;
use App\Infrastructure\Filament\Resources\Contests\Tables\ContestsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContestResource extends Resource
{
    protected static ?string $model = Contest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'gara';

    protected static ?string $pluralModelLabel = 'gare';

    public static function form(Schema $schema): Schema
    {
        return ContestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContests::route('/'),
            'create' => CreateContest::route('/create'),
            'edit' => EditContest::route('/{record}/edit'),
        ];
    }
}
