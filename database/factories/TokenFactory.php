<?php

namespace Database\Factories;

use App\Models\Token;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Token>
 */
class TokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Token::class;

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
            'user_id' => $this->faker->uuid(),
            'last_used_at' => $this->faker->dateTime(),
            'created_at' => $this->faker->dateTime(),
            'expires_at' => $this->faker->dateTime(),
        ];
    }
}
