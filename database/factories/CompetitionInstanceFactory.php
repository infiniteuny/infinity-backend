<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\CompetitionInstance;
use App\Models\CompetitionOrganizerType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
            'competition_id' => Competition::factory(),
            'name' => Str::title($this->faker->name()),
            'shortname' => Str::upper($this->faker->lexify('???')),
            'description' => $this->faker->text(),
            'url' => $this->faker->url(),
            'organizer' => $this->faker->company(),
            'organizer_type_id' => CompetitionOrganizerType::factory(),
            'logo' => json_encode([
                'disk' => 'local',
                'visibility' => 'private',
                'path' => 'competition-instances/logos',
                'name' => 'some-logo-id.jpg',
            ]),
            'start_date' => $this->faker->date(),
            'end_date' => fn (array $attributes) => Carbon::parse($attributes['start_date'])
                ->addDays($this->faker->numberBetween(0, 30))
                ->format('Y-m-d'),
            'location' => $this->faker->city(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
