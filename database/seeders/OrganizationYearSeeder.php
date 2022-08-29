<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\OrganizationYear;

class OrganizationYearSeeder extends Seeder
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
                "year" => 2020,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "year" => 2021,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "year" => 2022,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ];

        foreach (array_chunk($data, 1000) as $t) {
            OrganizationYear::insert($t);
        }
    }
}
