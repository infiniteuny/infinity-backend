<?php

namespace Database\Seeders;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $currentDateTime = Carbon::now()->format(DATE_ATOM);

        Degree::insert([
            [
                'code' => '1',
                'name' => 'Diploma (I, II, atau III)',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '2',
                'name' => 'Sarjana Terapan',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '3',
                'name' => 'Sarjana',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '4',
                'name' => 'Magister',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '5',
                'name' => 'Doktor',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '6',
                'name' => 'Profesi',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '7',
                'name' => 'Nongelar',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '8',
                'name' => 'Transfer Kredit',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
        ]);
        Faculty::create([
            [
                'code' => '01',
                'name' => 'Fakultas Ilmu Pendidikan dan Psikologi',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '02',
                'name' => 'Fakultas Bahasa, Seni, dan Budaya',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '03',
                'name' => 'Fakultas Matematika dan Ilmu Pengetahuan Alam',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '04',
                'name' => 'Fakultas Ilmu Sosial, Hukum, dan Ilmu Politik',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '05',
                'name' => 'Fakultas Teknik',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '06',
                'name' => 'Fakultas Ilmu Keolahragaan dan Kesehatan',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '07',
                'name' => 'Sekolah Pascasarjana',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '08',
                'name' => 'Fakultas Ekonomi dan Bisnis',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '09',
                'name' => 'Fakultas Vokasi',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
            [
                'code' => '10',
                'name' => 'Program Profesi',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ],
        ]);
        Major::insert([
            [
                'degree_id' => '1',
                'faculty_id' => '1',
                'code' => '01',
                'name' => 'Manajemen Pendidikan',
            ],

        ]);
    }
}
