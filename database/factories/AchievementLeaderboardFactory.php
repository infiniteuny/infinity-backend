<?php

namespace Database\Factories;

use App\Models\AchievementLeaderboard;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<AchievementLeaderboard>
 */
class AchievementLeaderboardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = AchievementLeaderboard::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'user_id' => $this->faker->uuid(),
            'year' => $this->faker->year(),
            'total_points' => $this->faker->numberBetween(0, 2147483647),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
