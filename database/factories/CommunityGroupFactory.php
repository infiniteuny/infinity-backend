<?php

namespace Database\Factories;

use App\Models\CommunityGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommunityGroup>
 */
class CommunityGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = CommunityGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'name' => $this->faker->unique()->word(),
            'priority' => $this->faker->numberBetween(),
            'description' => $this->faker->text(),
            'logo' => json_encode([
                'disk' => 'local',
                'visibility' => 'public',
                'path' => 'community-groups/logos',
                'name' => 'some-logo-id.jpg',
            ]),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
