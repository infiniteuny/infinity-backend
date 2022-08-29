<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Degree;

class DegreeSeeder extends Seeder
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
                "code" => 4,
                "name" => "S1",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 5,
                "name" => "S2",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 6,
                "name" => "S3",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 3,
                "name" => "D4",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 9,
                "name" => "PPI",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "code" => 2,
                "name" => "S1",
                "created_at" => now(),
                "updated_at" => now(),
            ]
        ];

        foreach (array_chunk($data, 1000) as $t) {
            Degree::insert($t);
        }
    }
}
