<?php

namespace Database\Factories;

use App\Models\CompetitionInstance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @extends Factory<CompetitionInstance>
 */
class CompetitionInstanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = CompetitionInstance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'competition_id' => $this->faker->uuid(),
            'name' => Str::title($this->faker->name()),
            'description' => $this->faker->text(),
            'url' => $this->faker->url(),
            'organizer' => $this->faker->company(),
            'organizer_type_id' => $this->faker->uuid(),
            'logo' => json_encode([
                'disk' => 'local',
                'visibility' => 'private',
                'path' => 'competition-instances/logos',
                'name' => 'some-logo-id.jpg',
            ]),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'location' => $this->faker->city(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
