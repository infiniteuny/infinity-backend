<?php

namespace Database\Factories;

use App\Models\Achievement;
use App\Models\Competition;
use App\Models\CompetitionOutput;
use App\Models\CompetitionRank;
use App\Models\CompetitionScale;
use App\Models\CompetitionTeamType;
use App\Models\CompetitionTimeRange;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
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
            'id' => $this->faker->uuid,
            'team_id' => Team::factory(),
            'competition_id' => Competition::factory(),
            'competition_team_type_id' => CompetitionTeamType::factory(),
            'competition_scale_id' => CompetitionScale::factory(),
            'competition_time_range_id' => CompetitionTimeRange::factory(),
            'competition_output_id' => CompetitionOutput::factory(),
            'competition_rank_id' => CompetitionRank::factory(),
            'competition_branch' => Str::title($this->faker->word),
            'competition_start_date' => $this->faker->date(),
            'competition_end_date' => $this->faker->date(),
            'description' => $this->faker->text,
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
