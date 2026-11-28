<?php

namespace Database\Factories;

use App\Models\Config;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Config>
 */
class ConfigFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Config::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'key' => strtolower($this->faker->unique()->lexify('config_????????')),
            'type' => $this->faker->randomElement(['STRING', 'INTEGER', 'BOOLEAN']),
            'value' => fn (array $attributes) => match ($attributes['type'] ?? 'STRING') {
                'INTEGER' => (string) $this->faker->numberBetween(0, 1000),
                'BOOLEAN' => $this->faker->boolean() ? 'true' : 'false',
                default => $this->faker->word(),
            },
            'is_private' => $this->faker->boolean(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
