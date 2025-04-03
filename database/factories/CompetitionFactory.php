<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\CompetitionOrganizerType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Competition>
 */
class CompetitionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Competition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'name' => Str::title($this->faker->name()),
            'description' => $this->faker->text(),
            'url' => $this->faker->url(),
            'organizer' => $this->faker->company(),
            'organizer_type_id' => CompetitionOrganizerType::factory(),
            'logo' => json_encode([
                'disk' => 'local',
                'visibility' => 'private',
                'path' => 'competitions/logos',
                'name' => 'some-logo-id.jpg',
            ]),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
