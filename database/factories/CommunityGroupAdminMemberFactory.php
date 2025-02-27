<?php

namespace Database\Factories;

use App\Models\CommunityGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\CommunityGroupAdminMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommunityGroupAdminMember>
 */
class CommunityGroupAdminMemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = CommunityGroupAdminMember::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'user_id' => User::factory(),
            'community_group_admin_id' => CommunityGroupAdmin::factory(),
            'community_group_id' => CommunityGroup::factory(),
            'photo' => 'some-photo-id',
            'animation' => 'some-animation-id',
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
