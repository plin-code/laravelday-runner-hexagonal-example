<?php

namespace App\Infrastructure\Filament\Resources\Runners\Pages;

use App\Domain\Runners\Models\Runner;
use App\Infrastructure\Filament\Resources\Runners\RunnerResource;
use App\Infrastructure\Filament\Resources\Runners\Schemas\RunnerForm;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class EditRunner extends EditRecord
{
    protected static string $resource = RunnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * Add the stored custom field values, keyed by slug, to the form state.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $runner = $this->getRecord();
        assert($runner instanceof Runner);

        $data[RunnerForm::CUSTOM_FIELDS] = $runner->getCustomFields();

        return $data;
    }

    /**
     * Save the runner and its custom fields. Filament wraps the save in a transaction,
     * so a refused custom field value rolls the runner back too.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        assert($record instanceof Runner);

        /** @var array<string, mixed> $customFields */
        $customFields = Arr::pull($data, RunnerForm::CUSTOM_FIELDS, []);

        $record->update($data);

        try {
            $record->setCustomFields($customFields, complete: true);
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages(
                Arr::mapWithKeys(
                    $exception->errors(),
                    fn (array $messages, string $slug): array => ['data.'.RunnerForm::CUSTOM_FIELDS.'.'.$slug => $messages],
                ),
            );
        }

        return $record;
    }
}
