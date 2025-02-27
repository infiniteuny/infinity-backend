<?php

namespace Database\Factories;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Major>
 */
class MajorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Major::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'degree_id' => Degree::factory(),
            'faculty_id' => Faculty::factory(),
            'code' => $this->faker->unique()->randomNumber(2),
            'name' => Str::title($this->faker->word()),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
