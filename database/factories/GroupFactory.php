<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Group::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'name' => Str::title($this->faker->unique()->word()),
            'guard_name' => 'oidc',
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }

    public function pivotUserGroup(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'entitlement' => [
                    'id' => $this->faker->uuid(),
                    'user_id' => $this->faker->uuid(),
                    'group_id' => $attributes['id'],
                ],
            ];
        });
    }
}
