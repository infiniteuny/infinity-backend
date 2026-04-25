<?php

namespace Database\Factories;

use App\Models\FundApplication;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
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
            'team_id' => $this->faker->uuid(),
            'competition_instance_id' => $this->faker->uuid(),
            'competition_team_type_id' => $this->faker->uuid(),
            'competition_scale_id' => $this->faker->uuid(),
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
