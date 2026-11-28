<?php

namespace Database\Factories;

use App\Models\CompetitionInstance;
use App\Models\CompetitionScale;
use App\Models\FundApplication;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<FundApplication>
 */
class FundApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
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
            'competition_instance_id' => CompetitionInstance::factory(),
            'competition_scale_id' => CompetitionScale::factory(),
            'competition_branch' => Str::title($this->faker->word()),
            'competition_start_date' => $this->faker->date(),
            'competition_end_date' => fn (array $attributes) => Carbon::parse($attributes['competition_start_date'])
                ->addDays($this->faker->numberBetween(0, 30))
                ->format('Y-m-d'),
            'letter_of_acceptance' => json_encode([
                'disk' => 'local',
                'visibility' => 'private',
                'path' => 'fund-applications/letter-of-acceptances',
                'name' => 'some-loa-id.pdf',
            ]),
            'proposal' => json_encode([
                'disk' => 'local',
                'visibility' => 'private',
                'path' => 'fund-applications/proposals',
                'name' => 'some-proposal-id.docx',
            ]),
            'status' => $this->faker->randomElement(['PENDING', 'REJECTED', 'ACCEPTED']),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
