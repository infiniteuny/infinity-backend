<?php

namespace Database\Factories;

use App\Models\Token;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Token>
 */
class TokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Token::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = Carbon::instance($this->faker->dateTimeBetween('-1 month', 'now'));

        return [
            'id' => $this->faker->uuid(),
            'sso_id' => $this->faker->unique()->uuid(),
            'user_id' => User::factory(),
            'last_used_at' => fn (array $attributes) => Carbon::parse($attributes['created_at'])
                ->addMinutes($this->faker->numberBetween(0, 60)),
            'created_at' => $createdAt,
            'expires_at' => fn (array $attributes) => Carbon::parse($attributes['created_at'])
                ->addDays($this->faker->numberBetween(1, 30)),
        ];
    }
}
