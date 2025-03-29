<?php

namespace Database\Factories;

use App\Models\Major;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
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
            'sso_id' => $this->faker->uuid(),
            'name' => Str::title($this->faker->name()),
            'email_address' => $this->faker->unique()->email(),
            'phone_number' => $this->faker->unique()->phoneNumber(),
            'student_id' => $this->faker->unique()->uuid(),
            'major_id' => Major::factory(),
            'links' => [
                'github' => $this->faker->url(),
                'linkedin' => $this->faker->url(),
                'website' => $this->faker->url(),
            ],
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'is_member' => $this->faker->boolean(),
            'is_extraordinary' => $this->faker->boolean(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
