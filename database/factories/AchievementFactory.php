<?php

namespace Database\Factories;

use App\Models\Achievement;
use App\Models\CompetitionInstance;
use App\Models\CompetitionOutput;
use App\Models\CompetitionRank;
use App\Models\CompetitionScale;
use App\Models\CompetitionTimeRange;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Achievement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'team_id' => Team::factory(),
            'competition_instance_id' => CompetitionInstance::factory(),
            'competition_scale_id' => CompetitionScale::factory(),
            'competition_time_range_id' => CompetitionTimeRange::factory(),
            'competition_output_id' => CompetitionOutput::factory(),
            'competition_rank_id' => CompetitionRank::factory(),
            'competition_branch' => Str::title($this->faker->word()),
            'competition_start_date' => $this->faker->date(),
            'competition_end_date' => fn (array $attributes) => Carbon::parse($attributes['competition_start_date'])
                ->addDays($this->faker->numberBetween(0, 30))
                ->format('Y-m-d'),
            'description' => $this->faker->text(),
            'image' => json_encode([
                'disk' => 'local',
                'visibility' => 'private',
                'path' => 'achievements/images',
                'name' => 'some-image-id.jpg',
            ]),
            'status' => $this->faker->randomElement(['PENDING', 'REJECTED', 'ACCEPTED']),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
