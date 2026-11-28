<?php

namespace Database\Factories;

use App\Models\CommunityGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @extends Factory<CommunityGroup>
 */
class CommunityGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
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
            'name' => Str::title($this->faker->unique()->words(2, true)),
            'priority' => $this->faker->numberBetween(1, 127),
            'description' => $this->faker->text(),
            'logo' => json_encode([
                'disk' => 'local',
                'visibility' => 'public',
                'path' => 'community-groups/logos',
                'name' => 'some-logo-id.jpg',
            ]),
            'is_active' => $this->faker->boolean(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }

    public function pivotCommunityGroupMember(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'membership' => [
                    'id' => $this->faker->uuid(),
                    'user_id' => $this->faker->uuid(),
                    'community_group_id' => $attributes['id'],
                    'created_at' => $this->faker->dateTime()->format(DATE_ATOM),
                    'updated_at' => $this->faker->dateTime()->format(DATE_ATOM),
                ],
            ];
        });
    }
}
