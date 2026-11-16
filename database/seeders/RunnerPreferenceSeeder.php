<?php

namespace Database\Seeders;

use App\Domain\Runners\Models\Runner;
use Illuminate\Database\Seeder;
use PlinCode\CustomFields\Facades\CustomFields;

class RunnerPreferenceSeeder extends Seeder
{
    /**
     * Define the preferences of a runner as custom fields.
     */
    public function run(): void
    {
        $fieldModel = CustomFields::fieldModel();
        $entity = CustomFields::entityKey(Runner::class);

        $preferences = [
            [
                'name' => 'Taglia maglietta',
                'type' => 'select',
                'options' => [
                    ['key' => 's', 'label' => 'S', 'is_active' => true],
                    ['key' => 'm', 'label' => 'M', 'is_active' => true],
                    ['key' => 'l', 'label' => 'L', 'is_active' => true],
                    ['key' => 'xl', 'label' => 'XL', 'is_active' => true],
                ],
            ],
            ['name' => 'Newsletter', 'type' => 'boolean'],
            ['name' => 'Contatto di emergenza', 'type' => 'phone'],
            ['name' => 'Note per gli organizzatori', 'type' => 'textarea'],
        ];

        foreach ($preferences as $index => $preference) {
            $fieldModel::query()->firstOrCreate(
                ['entity_type' => $entity, 'name' => mb_strtolower($preference['name'])],
                [
                    'type' => $preference['type'],
                    'options' => $preference['options'] ?? null,
                    'sort_order' => ($index + 1) * 10,
                ],
            );
        }
    }
}
