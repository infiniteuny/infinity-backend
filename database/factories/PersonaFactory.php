<?php

namespace Database\Factories;

use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Persona>
 */
class PersonaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Persona::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'name' => Str::title($this->faker->unique()->word()),
            'priority' => $this->faker->numberBetween(1, 127),
            'description' => $this->faker->text(),
            'logo' => json_encode([
                'disk' => 'local',
                'visibility' => 'public',
                'path' => 'personas/logos',
                'name' => 'some-logo-id.jpg',
            ]),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }

    public function pivotUserPersona(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'entitlement' => [
                    'id' => $this->faker->uuid(),
                    'user_id' => $this->faker->uuid(),
                    'persona_id' => $attributes['id'],
                    'created_at' => $this->faker->dateTime()->format(DATE_ATOM),
                    'updated_at' => $this->faker->dateTime()->format(DATE_ATOM),
                ],
            ];
        });
    }
}
