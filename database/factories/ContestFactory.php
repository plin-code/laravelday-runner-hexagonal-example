<?php

namespace Database\Factories;

use App\Domain\Contests\Models\Contest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contest>
 */
class ContestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Contest>
     */
    protected $model = Contest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city().' Run',
            'date' => fake()->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
            'available_places' => fake()->numberBetween(50, 500),
            'registrations_open' => true,
        ];
    }

    /**
     * Indicate that the contest does not accept registrations.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'registrations_open' => false,
        ]);
    }
}
