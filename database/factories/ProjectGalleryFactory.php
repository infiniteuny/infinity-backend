<?php

namespace Database\Factories;

use App\Models\ProjectGallery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectGallery>
 */
class ProjectGalleryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = ProjectGallery::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'title' => Str::title($this->faker->sentence()),
            'description' => $this->faker->paragraph(),
            'url' => $this->faker->url(),
            'image' => json_encode([
                'disk' => 'local',
                'visibility' => 'public',
                'path' => 'project-galleries/images',
                'name' => 'some-image-id.jpg',
            ]),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
