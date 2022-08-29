<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Faculty;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                "id" => Str::uuid()->toString(),
                "code" => 1,
                "name" => "Ilmu Pendidikan",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 2,
                "name" => "Bahasa dan Seni",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 3,
                "name" => "Matematika dan Ilmu Pengetahuan Alam",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 4,
                "name" => "Ilmu Sosial",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 5,
                "name" => "Teknik",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 6,
                "name" => "Ilmu Keolahragaan",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 7,
                "name" => "Pascasarjana",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 8,
                "name" => "Ekonomi",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ];

        foreach (array_chunk($data, 1000) as $t) {
            Faculty::insert($t);
        }
    }
}
