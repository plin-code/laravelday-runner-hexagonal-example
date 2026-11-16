<?php

namespace Database\Factories;

use App\Domain\Runners\Models\Runner;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Runner>
 */
class RunnerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Runner>
     */
    protected $model = Runner::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'birth_date' => fake()->dateTimeBetween('-70 years', '-20 years')->format('Y-m-d'),
            'user_id' => fn (array $attributes) => User::factory()->state([
                'name' => $attributes['first_name'].' '.$attributes['last_name'],
            ]),
        ];
    }
}
