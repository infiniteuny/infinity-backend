<?php

namespace Database\Factories;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'id' => $this->faker->uuid(),
            'team_id' => $this->faker->uuid(),
            'competition_id' => $this->faker->uuid(),
            'competition_team_type_id' => $this->faker->uuid(),
            'competition_scale_id' => $this->faker->uuid(),
            'competition_time_range_id' => $this->faker->uuid(),
            'competition_output_id' => $this->faker->uuid(),
            'competition_rank_id' => $this->faker->uuid(),
            'competition_branch' => Str::title($this->faker->word()),
            'competition_start_date' => $this->faker->date(),
            'competition_end_date' => $this->faker->date(),
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
