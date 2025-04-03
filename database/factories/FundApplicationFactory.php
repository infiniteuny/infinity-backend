<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\CompetitionScale;
use App\Models\CompetitionTeamType;
use App\Models\FundApplication;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FundApplication>
 */
class FundApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = FundApplication::class;

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
            'competition_id' => Competition::factory(),
            'competition_team_type_id' => CompetitionTeamType::factory(),
            'competition_scale_id' => CompetitionScale::factory(),
            'competition_branch' => Str::title($this->faker->word()),
            'competition_start_date' => $this->faker->date(),
            'competition_end_date' => $this->faker->date(),
            'letter_of_acceptance' => json_encode([
                'disk' => 'local',
                'visibility' => 'private',
                'path' => 'fund-applications/letters-of-acceptances',
                'name' => 'some-loa-id.pdf',
            ]),
            'proposal' => json_encode([
                'disk' => 'local',
                'visibility' => 'private',
                'path' => 'fund-applications/proposals',
                'name' => 'some-proposal-id.pdf',
            ]),
            'status' => $this->faker->randomElement(['PENDING', 'REJECTED', 'ACCEPTED']),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
