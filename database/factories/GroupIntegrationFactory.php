<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupIntegration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<GroupIntegration>
 */
class GroupIntegrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = GroupIntegration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'group_id' => Group::factory(),
            'docs_group_id' => $this->faker->unique()->uuid(),
            'docs_collection_id' => $this->faker->unique()->uuid(),
            'docs_group_last_synced_at' => $this->faker->dateTime(),
            'docs_collection_last_synced_at' => $this->faker->dateTime(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
