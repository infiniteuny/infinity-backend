<?php

namespace Database\Factories;

use App\Facades\Storage;
use App\Models\Major;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'sso_id' => (string) $this->faker->unique()->numberBetween(100000, 999999999),
            'name' => Str::title($this->faker->name()),
            'username' => Str::limit($this->faker->unique()->userName(), 150, ''),
            'email_address' => $this->faker->unique()->email(),
            'phone_number' => $this->faker->unique()->phoneNumber(),
            'student_id' => sprintf('%011d', $this->faker->unique()->numberBetween(10000000000, 99999999999)),
            'major_id' => Major::factory(),
            'links' => [
                'github' => $this->faker->userName(),
                'linkedin' => $this->faker->userName(),
                'website' => $this->faker->url(),
            ],
            'start_date' => $this->faker->date(),
            'end_date' => fn (array $attributes) => $attributes['start_date'] !== null
                ? Carbon::parse($attributes['start_date'])->addDays($this->faker->numberBetween(0, 365))->format('Y-m-d')
                : null,
            'is_member' => $this->faker->boolean(),
            'is_extraordinary' => $this->faker->boolean(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }

    public function pivotTeamMember(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'membership' => [
                    'id' => $this->faker->uuid(),
                    'user_id' => $attributes['id'],
                    'team_id' => $this->faker->uuid(),
                    'created_at' => $this->faker->dateTime()->format(DATE_ATOM),
                    'updated_at' => $this->faker->dateTime()->format(DATE_ATOM),
                ],
            ];
        });
    }

    public function pivotCoreTeamMember(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'membership' => [
                    'id' => $this->faker->uuid(),
                    'user_id' => $attributes['id'],
                    'core_team_id' => $this->faker->uuid(),
                    'core_team_division_id' => $this->faker->uuid(),
                    'photo' => Storage::url(json_encode([
                        'disk' => 'local',
                        'visibility' => 'public',
                        'path' => 'core-teams/photos',
                        'name' => 'some-photo-id.jpg',
                    ])),
                    'animation' => Storage::url(json_encode([
                        'disk' => 'local',
                        'visibility' => 'public',
                        'path' => 'core-teams/animations',
                        'name' => 'some-animation-id.gif',
                    ])),
                    'created_at' => $this->faker->dateTime()->format(DATE_ATOM),
                    'updated_at' => $this->faker->dateTime()->format(DATE_ATOM),
                ],
            ];
        });
    }

    public function pivotCommunityGroupMember(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'membership' => [
                    'id' => $this->faker->uuid(),
                    'user_id' => $attributes['id'],
                    'community_group_id' => $this->faker->uuid(),
                    'created_at' => $this->faker->dateTime()->format(DATE_ATOM),
                    'updated_at' => $this->faker->dateTime()->format(DATE_ATOM),
                ],
            ];
        });
    }

    public function pivotCommunityGroupAdminMember(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'membership' => [
                    'id' => $this->faker->uuid(),
                    'user_id' => $attributes['id'],
                    'community_group_admin_id' => $this->faker->uuid(),
                    'community_group_id' => $this->faker->uuid(),
                    'photo' => Storage::url(json_encode([
                        'disk' => 'local',
                        'visibility' => 'public',
                        'path' => 'community-group-admins/photos',
                        'name' => 'some-photo-id.jpg',
                    ])),
                    'animation' => Storage::url(json_encode([
                        'disk' => 'local',
                        'visibility' => 'public',
                        'path' => 'community-group-admins/animations',
                        'name' => 'some-animation-id.gif',
                    ])),
                    'created_at' => $this->faker->dateTime()->format(DATE_ATOM),
                    'updated_at' => $this->faker->dateTime()->format(DATE_ATOM),
                ],
            ];
        });
    }
}
