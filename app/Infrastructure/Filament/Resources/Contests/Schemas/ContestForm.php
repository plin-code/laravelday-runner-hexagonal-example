<?php

namespace App\Infrastructure\Filament\Resources\Contests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class ContestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('date', $get('date')),
                    )
                    ->validationMessages([
                        'unique' => 'Esiste già una gara con questo nome in questa data.',
                    ]),
                DatePicker::make('date')
                    ->label('Data')
                    ->required(),
                TextInput::make('available_places')
                    ->label('Posti totali')
                    ->required()
                    ->integer()
                    ->minValue(0),
                Toggle::make('registrations_open')
                    ->label('Iscrizioni aperte')
                    ->default(true),
            ]);
    }
}
