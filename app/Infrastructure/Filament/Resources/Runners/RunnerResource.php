<?php

namespace App\Infrastructure\Filament\Resources\Runners;

use App\Domain\Runners\Models\Runner;
use App\Infrastructure\Filament\Resources\Runners\Pages\EditRunner;
use App\Infrastructure\Filament\Resources\Runners\Pages\ListRunners;
use App\Infrastructure\Filament\Resources\Runners\Schemas\RunnerForm;
use App\Infrastructure\Filament\Resources\Runners\Tables\RunnersTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RunnerResource extends Resource
{
    protected static ?string $model = Runner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $modelLabel = 'runner';

    protected static ?string $pluralModelLabel = 'runner';

    public static function form(Schema $schema): Schema
    {
        return RunnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RunnersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRunners::route('/'),
            'edit' => EditRunner::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<Model>
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
