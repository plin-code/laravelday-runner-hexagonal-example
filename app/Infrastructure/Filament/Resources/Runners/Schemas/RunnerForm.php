<?php

namespace App\Infrastructure\Filament\Resources\Runners\Schemas;

use App\Domain\Runners\Models\Runner;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use PlinCode\CustomFields\Facades\CustomFields;
use PlinCode\CustomFields\Models\CustomField;

class RunnerForm
{
    /**
     * State path of the section holding the custom fields, keyed by slug.
     */
    public const string CUSTOM_FIELDS = 'custom_fields';

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Anagrafica')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('first_name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('last_name')
                            ->label('Cognome')
                            ->required()
                            ->maxLength(100),
                        DatePicker::make('birth_date')
                            ->label('Data di nascita')
                            ->required()
                            ->maxDate(now()),
                        TextInput::make('email')
                            ->label('Email')
                            ->helperText("L'email appartiene all'utente e non si modifica da qui.")
                            ->formatStateUsing(fn (?Runner $record): ?string => $record?->user->email)
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make('Preferenze')
                    ->columns(2)
                    ->columnSpanFull()
                    ->statePath(self::CUSTOM_FIELDS)
                    ->schema(fn (): array => self::customFieldComponents()),
            ]);
    }

    /**
     * One component for each active custom field of the runner, named after its slug.
     *
     * @return list<Field>
     */
    private static function customFieldComponents(): array
    {
        $fieldModel = CustomFields::fieldModel();

        return array_values($fieldModel::query()
            ->where('entity_type', CustomFields::entityKey(Runner::class))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CustomField $field): Field => self::componentFor(
                slug: $field->getAttribute('slug'),
                type: $field->getAttribute('type'),
                options: collect($field->optionsForInput())->pluck('label', 'key')->all(),
            )
                ->label(Str::ucfirst($field->getAttribute('name')))
                ->required($field->getAttribute('is_required') && $field->getAttribute('type') !== 'boolean'))
            ->all());
    }

    /**
     * The component that fits a custom field type. A boolean is a toggle, which always
     * holds an answer, so it is never marked as required.
     *
     * @param  array<array-key, mixed>  $options
     */
    private static function componentFor(string $slug, string $type, array $options): Field
    {
        return match ($type) {
            'textarea' => Textarea::make($slug)->columnSpanFull(),
            'number' => TextInput::make($slug)->integer(),
            'decimal' => TextInput::make($slug)->numeric(),
            'boolean' => Toggle::make($slug),
            'date' => DatePicker::make($slug),
            'datetime' => DateTimePicker::make($slug),
            'select' => Select::make($slug)->options($options),
            'multiselect' => Select::make($slug)->options($options)->multiple(),
            'email' => TextInput::make($slug)->email()->maxLength(255),
            'url' => TextInput::make($slug)->url()->maxLength(255),
            'phone' => TextInput::make($slug)->tel()->maxLength(255),
            default => TextInput::make($slug)->maxLength(255),
        };
    }
}
