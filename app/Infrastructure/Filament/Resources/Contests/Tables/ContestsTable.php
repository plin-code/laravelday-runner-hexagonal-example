<?php

namespace App\Infrastructure\Filament\Resources\Contests\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('available_places')
                    ->label('Posti totali')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('runners_count')
                    ->label('Iscritti')
                    ->counts('runners')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('registrations_open')
                    ->label('Iscrizioni aperte')
                    ->boolean(),
            ])
            ->defaultSort('date')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
