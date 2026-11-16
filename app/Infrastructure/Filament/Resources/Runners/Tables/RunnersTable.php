<?php

namespace App\Infrastructure\Filament\Resources\Runners\Tables;

use App\Domain\Runners\Models\Runner;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RunnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nome e cognome')
                    ->state(fn (Runner $record): string => "{$record->first_name} {$record->last_name}")
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->searchFullName($search))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByFullName($direction)),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->label('Data di nascita')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('contests_count')
                    ->label('Gare')
                    ->counts('contests')
                    ->numeric(),
                TextColumn::make('deleted_at')
                    ->label('Cancellato il')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
