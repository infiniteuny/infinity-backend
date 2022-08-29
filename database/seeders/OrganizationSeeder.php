<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Organization;
use App\Models\OrganizationYear;

class OrganizationSeeder extends Seeder
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
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Devaanu Arkaan Dirgantara",
                "student_id" => 21602334001,
                "position" => "Staff Research And Development",
                "avatar" =>
                Str::of('Devaanu Arkaan Dirgantara')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Syahrul Maula 'Azmi",
                "student_id" => 21520241002,
                "position" => "Staff Media And Information",
                "avatar" => Str::of('Syahrul Maula \'Azmi')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Wisnu Wijonarko",
                "student_id" => 21501244001,
                "position" => "Staff Adminkeu",
                "avatar" => Str::of('Wisnu Wijonarko')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Muhammad Ridho Al Fajri",
                "student_id" => 21506334039,
                "position" => "Staff Research And Development",
                "avatar" =>
                Str::of('Muhammad Ridho Al Fajri')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Melati Wangi Windari",
                "student_id" => 21507334064,
                "position" => "Staff Adminkeu",
                "avatar" => Str::of('Melati Wangi Windari')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Anandika Marsa Jordan",
                "student_id" => 20105244024,
                "position" => "Staff Entrepreneurship",
                "avatar" => Str::of('Anandika Marsa Jordan')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Ayu Syifa Rohmah",
                "student_id" => 20520241015,
                "position" => "Staff Entrepreneurship",
                "avatar" => Str::of('Ayu Syifa Rohmah')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Aniqah Nuha Hamizah",
                "student_id" => 21206244017,
                "position" => "Staff Media And Information",
                "avatar" => Str::of('Aniqah Nuha Hamizah')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Diana Nadia Maulida",
                "student_id" => 21537141002,
                "position" => "Staff Competition",
                "avatar" => Str::of('Diana Nadia Maulida')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Indah Risky Larasati",
                "student_id" => 21520244015,
                "position" => "Staff Adminkeu",
                "avatar" => Str::of('Indah Risky Larasati')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Sekar Muflihya Adi Utami",
                "student_id" => 20501241029,
                "position" => "Staff Research And Development",
                "avatar" =>
                Str::of('Sekar Muflihya Adi Utami')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Dio Faziko Pratama",
                "student_id" => 21507334017,
                "position" => "Staff Entrepreneurship",
                "avatar" => Str::of('Dio Faziko Pratama')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Hanifah Mar'atush Shalihah",
                "student_id" => 20301244022,
                "position" => "Staff Media And Information",
                "avatar" =>
                Str::of('Hanifah Mar\'atush Shalihah')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Enrico Olivian Maricar",
                "student_id" => 20537141007,
                "position" => "Staff Research And Development",
                "avatar" =>
                Str::of('Enrico Olivian Maricar')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Akmal Firmansyah",
                "student_id" => 20401244007,
                "position" => "Staff Competition",
                "avatar" => Str::of('Akmal Firmansyah')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Adinda Dwi Lestari",
                "student_id" => 20306141042,
                "position" => "Staff Adminkeu",
                "avatar" => Str::of('Adinda Dwi Lestari')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Satya Adhiyaksa Ardy",
                "student_id" => 20537144013,
                "position" => "Kepala Divisi",
                "avatar" => Str::of('Satya Adhiyaksa Ardy')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Maria Charlotta",
                "student_id" => 20537144022,
                "position" => "Wakil Kepala Divisi",
                "avatar" => Str::of('Maria Charlotta')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Dany Christian",
                "student_id" => 20520241021,
                "position" => "Ketua Tim",
                "avatar" => Str::of('Dany Christian')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Anisatul Afita",
                "student_id" => 20416244028,
                "position" => "Adminkeu",
                "avatar" => Str::of('Anisatul Afita')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Salsabila Rizki Prasasti",
                "student_id" => 20302241031,
                "position" => "Media And Information",
                "avatar" =>
                Str::of('Salsabila Rizki Prasasti')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Widya Ardianto",
                "student_id" => 20537141021,
                "position" => "Research And Development",
                "avatar" => Str::of('Widya Ardianto')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Ikhwan Inzaghi Siswanto",
                "student_id" => 20537141013,
                "position" => "Entrepreneurship",
                "avatar" =>
                Str::of('Ikhwan Inzaghi Siswanto')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => Str::uuid()->toString(),
                "organization_year_id" =>
                OrganizationYear::where('year', 2022)->first()->id,
                "name" => "Daffa Stefian Abyansyah",
                "student_id" => 20537141022,
                "position" => "Competition",
                "avatar" =>
                Str::of('Daffa Stefian Abyansyah')->slug('-') . '.png',
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ];

        foreach (array_chunk($data, 1000) as $t) {
            Organization::insert($t);
        }
    }
}
