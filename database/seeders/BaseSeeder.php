<?php

namespace Database\Seeders;

use App\Models\CommunityGroup;
use App\Models\CompetitionOrganizerType;
use App\Models\CompetitionOutput;
use App\Models\CompetitionRank;
use App\Models\CompetitionScale;
use App\Models\CompetitionTeamType;
use App\Models\CompetitionTimeRange;
use App\Models\CoreTeamDivision;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $degree = Degree::createMany([
            [
                'code' => '1',
                'name' => 'Diploma (I, II, atau III)',
            ],
            [
                'code' => '2',
                'name' => 'Sarjana Terapan',
            ],
            [
                'code' => '3',
                'name' => 'Sarjana',
            ],
            [
                'code' => '4',
                'name' => 'Magister',
            ],
            [
                'code' => '5',
                'name' => 'Doktor',
            ],
            [
                'code' => '6',
                'name' => 'Profesi',
            ],
            [
                'code' => '7',
                'name' => 'Nongelar',
            ],
            [
                'code' => '8',
                'name' => 'Transfer Kredit',
            ],
        ]);
        $faculty = Faculty::createMany([
            [
                'code' => '01',
                'name' => 'Fakultas Ilmu Pendidikan dan Psikologi',
            ],
            [
                'code' => '02',
                'name' => 'Fakultas Bahasa, Seni, dan Budaya',
            ],
            [
                'code' => '03',
                'name' => 'Fakultas Matematika dan Ilmu Pengetahuan Alam',
            ],
            [
                'code' => '04',
                'name' => 'Fakultas Ilmu Sosial, Hukum, dan Ilmu Politik',
            ],
            [
                'code' => '05',
                'name' => 'Fakultas Teknik',
            ],
            [
                'code' => '06',
                'name' => 'Fakultas Ilmu Keolahragaan dan Kesehatan',
            ],
            [
                'code' => '07',
                'name' => 'Sekolah Pascasarjana',
            ],
            [
                'code' => '08',
                'name' => 'Fakultas Ekonomi dan Bisnis',
            ],
            [
                'code' => '09',
                'name' => 'Fakultas Vokasi',
            ],
            [
                'code' => '10',
                'name' => 'Program Profesi',
            ],
        ]);
        Major::createMany([
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0101',
                'name' => 'Manajemen Pendidikan',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0102',
                'name' => 'Pendidikan Luar Sekolah',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0103',
                'name' => 'Pendidikan Luar Biasa',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0104',
                'name' => 'Bimbingan dan Konseling',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0105',
                'name' => 'Teknologi Pendidikan',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0106',
                'name' => 'Pendidikan Guru Sekolah Dasar',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0107',
                'name' => 'Kebijakan Pendidikan',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0108',
                'name' => 'Pendidikan Guru Pendidikan Anak Usia Dini',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0109',
                'name' => 'Psikologi',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0110',
                'name' => 'Pendidikan Dasar',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0111',
                'name' => 'Teknologi Pembelajaran',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0112',
                'name' => 'Pendidikan Luar Biasa',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0113',
                'name' => 'Psikologi',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0114',
                'name' => 'Pendidikan Luar Sekolah',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0115',
                'name' => 'Bimbingan dan Konseling',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0116',
                'name' => 'Manajemen Pendidikan',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0117',
                'name' => 'Pendidikan Anak Usia Dini',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0118',
                'name' => 'Kebijakan Pendidikan',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0119',
                'name' => 'Manajemen Pendidikan',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0120',
                'name' => 'Pendidikan Dasar',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '01')->first()->id,
                'code' => '0121',
                'name' => 'Bimbingan dan Konseling',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0201',
                'name' => 'Pendidikan Bahasa dan Sastra Indonesia',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0202',
                'name' => 'Pendidikan Bahasa Jawa',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0203',
                'name' => 'Pendidikan Bahasa Inggris',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0204',
                'name' => 'Pendidikan Bahasa Jerman',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0205',
                'name' => 'Pendidikan Bahasa Perancis',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0206',
                'name' => 'Pendidikan Kriya/Pendidikan Seni Kerajinan',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0207',
                'name' => 'Pendidikan Seni Musik',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0208',
                'name' => 'Pendidikan Seni Rupa',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0209',
                'name' => 'Pendidikan Seni Tari',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0210',
                'name' => 'Sastra Indonesia',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0211',
                'name' => 'Sastra Inggris',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0212',
                'name' => 'Pendidikan Bahasa dan Sastra Indonesia',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0213',
                'name' => 'Pendidikan Bahasa Inggris',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0214',
                'name' => 'Pendidikan Bahasa Jawa',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0215',
                'name' => 'Linguistik Terapan',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0216',
                'name' => 'Pendidikan Seni',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0217',
                'name' => 'Ilmu Pendidikan Bahasa',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '02')->first()->id,
                'code' => '0218',
                'name' => 'Pendidikan Bahasa Indonesia',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0301',
                'name' => 'Pendidikan Matematika',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0302',
                'name' => 'Pendidikan Fisika',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0303',
                'name' => 'Pendidikan Kimia',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0304',
                'name' => 'Pendidikan Biologi',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0305',
                'name' => 'Pendidikan Ilmu Pengetahuan Alam',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0306',
                'name' => 'Matematika',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0307',
                'name' => 'Fisika',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0308',
                'name' => 'Biologi',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0309',
                'name' => 'Kimia',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0310',
                'name' => 'Statistika',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0311',
                'name' => 'Pendidikan Matematika',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0312',
                'name' => 'Pendidikan Fisika',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0313',
                'name' => 'Pendidikan Biologi',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0314',
                'name' => 'Pendidikan Sains',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0315',
                'name' => 'Pendidikan Kimia',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0316',
                'name' => 'Pendidikan Kimia',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0317',
                'name' => 'Pendidikan Matematika',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '03')->first()->id,
                'code' => '0318',
                'name' => 'Pendidikan IPA',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0401',
                'name' => 'Pendidikan Pancasila dan Kewarganegaraan',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0402',
                'name' => 'Pendidikan Geografi',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0403',
                'name' => 'Pendidikan Sejarah',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0404',
                'name' => 'Pendidikan Sosiologi',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0405',
                'name' => 'Pendidikan Ilmu Pengetahuan Sosial',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0406',
                'name' => 'Ilmu Sejarah',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0407',
                'name' => 'Administrasi Publik',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0408',
                'name' => 'Ilmu Komunikasi',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0409',
                'name' => 'Pendidikan Ilmu Pengetahuan Sosial',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0410',
                'name' => 'Pendidikan Sejarah',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0411',
                'name' => 'Pendidikan Pancasila dan Kewarganegaraan',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '04')->first()->id,
                'code' => '0412',
                'name' => 'Pendidikan Geografi',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0501',
                'name' => 'Pendidikan Tata Boga',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0502',
                'name' => 'Pendidikan Tata Busana',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0503',
                'name' => 'Pendidikan Teknik Elektro',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0504',
                'name' => 'Pendidikan Teknik Elektronika',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0505',
                'name' => 'Pendidikan Teknik Informatika',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0506',
                'name' => 'Pendidikan Teknik Mekatronika',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0507',
                'name' => 'Pendidikan Teknik Mesin',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0508',
                'name' => 'Pendidikan Teknik Otomotif',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0509',
                'name' => 'Pendidikan Teknik Sipil dan Perencanaan',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0510',
                'name' => 'Teknik Elektro',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0511',
                'name' => 'Teknologi Informasi',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0512',
                'name' => 'Teknik Manufaktur',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0513',
                'name' => 'Teknik Sipil',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0514',
                'name' => 'Teknik Industri',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0515',
                'name' => 'Arsitektur',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0516',
                'name' => 'Pendidikan Teknik Elektronika & Informatika',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0517',
                'name' => 'Pendidikan Teknik Elektro',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0518',
                'name' => 'Pendidikan Teknik Mesin',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '05')->first()->id,
                'code' => '0519',
                'name' => 'Pendidikan Kesejahteraan Keluarga',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '06')->first()->id,
                'code' => '0601',
                'name' => 'Ilmu Keolahragaan',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '06')->first()->id,
                'code' => '0602',
                'name' => 'Pendidikan Jasmani, Kesehatan dan Rekreasi',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '06')->first()->id,
                'code' => '0603',
                'name' => 'Pendidikan Kepelatihan Olahraga',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '06')->first()->id,
                'code' => '0604',
                'name' => 'PGSD Pendidikan Jasmani',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '06')->first()->id,
                'code' => '0605',
                'name' => 'Ilmu Keolahragaan',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '06')->first()->id,
                'code' => '0606',
                'name' => 'Pendidikan Kepelatihan Olahraga',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '06')->first()->id,
                'code' => '0607',
                'name' => 'Pendidikan Jasmani',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '06')->first()->id,
                'code' => '0608',
                'name' => 'Pendidikan Jasmani Sekolah Dasar',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '06')->first()->id,
                'code' => '0609',
                'name' => 'Ilmu Keolahragaan',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '06')->first()->id,
                'code' => '0610',
                'name' => 'Pendidikan Jasmani',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '06')->first()->id,
                'code' => '0611',
                'name' => 'Pendidikan Kepelatihan Olahraga',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '07')->first()->id,
                'code' => '0701',
                'name' => 'Pendidikan Teknologi dan Kejuruan',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '07')->first()->id,
                'code' => '0702',
                'name' => 'Penelitian dan Evaluasi Pendidikan',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '07')->first()->id,
                'code' => '0703',
                'name' => 'Ilmu Pendidikan',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '07')->first()->id,
                'code' => '0704',
                'name' => 'Pendidikan Teknologi dan Kejuruan',
            ],
            [
                'degree_id' => $degree->where('code', '5')->first()->id,
                'faculty_id' => $faculty->where('code', '07')->first()->id,
                'code' => '0705',
                'name' => 'Penelitian dan Evaluasi Pendidikan',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '08')->first()->id,
                'code' => '0801',
                'name' => 'Akuntansi',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '08')->first()->id,
                'code' => '0802',
                'name' => 'Manajemen',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '08')->first()->id,
                'code' => '0803',
                'name' => 'Pendidikan Administrasi Perkantoran',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '08')->first()->id,
                'code' => '0804',
                'name' => 'Pendidikan Akuntansi',
            ],
            [
                'degree_id' => $degree->where('code', '3')->first()->id,
                'faculty_id' => $faculty->where('code', '08')->first()->id,
                'code' => '0805',
                'name' => 'Pendidikan Ekonomi',
            ],
            [
                'degree_id' => $degree->where('code', '4')->first()->id,
                'faculty_id' => $faculty->where('code', '08')->first()->id,
                'code' => '0806',
                'name' => 'Pendidikan Ekonomi',
            ],
            [
                'degree_id' => $degree->where('code', '1')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0901',
                'name' => 'Mesin Otomotif',
            ],
            [
                'degree_id' => $degree->where('code', '1')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0902',
                'name' => 'Tata Boga',
            ],
            [
                'degree_id' => $degree->where('code', '1')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0903',
                'name' => 'Tata Busana',
            ],
            [
                'degree_id' => $degree->where('code', '1')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0904',
                'name' => 'Tata Rias dan Kecantikan',
            ],
            [
                'degree_id' => $degree->where('code', '1')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0905',
                'name' => 'Teknik Elektro',
            ],
            [
                'degree_id' => $degree->where('code', '1')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0906',
                'name' => 'Teknik Elektronika',
            ],
            [
                'degree_id' => $degree->where('code', '1')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0907',
                'name' => 'Teknik Mesin',
            ],
            [
                'degree_id' => $degree->where('code', '1')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0908',
                'name' => 'Teknik Sipil',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0909',
                'name' => 'Mesin Otomotif',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0910',
                'name' => 'Tata Boga',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0911',
                'name' => 'Tata Busana',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0912',
                'name' => 'Tata Rias dan Kecantikan',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0913',
                'name' => 'Teknik Elektro',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0914',
                'name' => 'Teknik Elektronika',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0915',
                'name' => 'Teknik Mesin',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0916',
                'name' => 'Teknik Sipil',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0917',
                'name' => 'Pengelolaan Usaha Rekreasi',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0918',
                'name' => 'Pengobatan Tradisional',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0919',
                'name' => 'Promosi Kesehatan',
            ],
            [
                'degree_id' => $degree->where('code', '1')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0920',
                'name' => 'Akuntansi',
            ],
            [
                'degree_id' => $degree->where('code', '1')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0921',
                'name' => 'Manajemen Pemasaran',
            ],
            [
                'degree_id' => $degree->where('code', '1')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0922',
                'name' => 'Administrasi Perkantoran',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0923',
                'name' => 'Akuntansi',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0924',
                'name' => 'Manajemen Pemasaran',
            ],
            [
                'degree_id' => $degree->where('code', '2')->first()->id,
                'faculty_id' => $faculty->where('code', '09')->first()->id,
                'code' => '0925',
                'name' => 'Administrasi Perkantoran',
            ],
        ]);
        Role::createMany([
            [
                'name' => 'Hustler',
                'priority' => 1,
                'description' => 'Seseorang yang memiliki peran dalam memperkenalkan dan memasarkan produk kepada konsumen. Keahlian utama: management, negotiation, writing, critical thinking.',
                'logo' => '',
            ],
            [
                'name' => 'Hipster',
                'priority' => 2,
                'description' => 'Seseorang yang memiliki peran dalam menyajikan dan memastikan estetika tampilan serta pengalaman pengguna dari sebuah produk. Keahlian utama: ideation, creativity, design.',
                'logo' => '',
            ],
            [
                'name' => 'Hacker',
                'priority' => 3,
                'description' => 'Seseorang yang memiliki peran dalam mengembangkan teknologi yang ada dalam produk. Keahlian utama: problem solving, analysis, programming.',
                'logo' => '',
            ],
        ]);
        CommunityGroup::createMany([
            [
                'name' => 'IT Business',
                'priority' => 1,
                'description' => 'Grup yang berfokus pada pengembangan bisnis berbasis teknologi informasi.',
                'logo' => '',
            ],
            [
                'name' => 'User Interface/User Experience',
                'priority' => 2,
                'description' => 'Grup yang berfokus pada pengembangan tampilan dan pengalaman pengguna.',
                'logo' => '',
            ],
            [
                'name' => 'Mobile Development',
                'priority' => 3,
                'description' => 'Grup yang berfokus pada pengembangan aplikasi berbasis mobile.',
                'logo' => '',
            ],
            [
                'name' => 'Front End Development',
                'priority' => 4,
                'description' => 'Grup yang berfokus pada pengembangan tampilan aplikasi berbasis web maupun desktop.',
                'logo' => '',
            ],
            [
                'name' => 'Back End Development',
                'priority' => 5,
                'description' => 'Grup yang berfokus pada pengembangan sistem dan database aplikasi.',
                'logo' => '',
            ],
            [
                'name' => 'Artificial Intelligence and Data Science',
                'priority' => 6,
                'description' => 'Grup yang berfokus pada pengembangan kecerdasan buatan dan ilmu data.',
                'logo' => '',
            ],
            [
                'name' => 'Cyber Security',
                'priority' => 7,
                'description' => 'Grup yang berfokus pada pengembangan keamanan sistem, data, dan jaringan.',
                'logo' => '',
            ],
        ]);
        CoreTeamDivision::createMany([
            [
                'name' => 'President',
                'priority' => 1,
            ],
            [
                'name' => 'Vice President',
                'priority' => 2,
            ],
            [
                'name' => 'Administration and Finance Leader',
                'priority' => 11,
            ],
            [
                'name' => 'Administration and Finance Staff',
                'priority' => 12,
            ],
            [
                'name' => 'Human Resources Development Leader',
                'priority' => 21,
            ],
            [
                'name' => 'Human Resources Development Staff',
                'priority' => 22,
            ],
            [
                'name' => 'Entrepreneurship and Partnership Leader',
                'priority' => 31,
            ],
            [
                'name' => 'Entrepreneurship and Partnership Staff',
                'priority' => 32,
            ],
            [
                'name' => 'Media and Information Leader',
                'priority' => 41,
            ],
            [
                'name' => 'Media and Information Staff',
                'priority' => 42,
            ],
            [
                'name' => 'Competition Leader',
                'priority' => 51,
            ],
            [
                'name' => 'Competition Staff',
                'priority' => 52,
            ],
            [
                'name' => 'Research and Development Leader',
                'priority' => 61,
            ],
            [
                'name' => 'Research and Development Staff',
                'priority' => 62,
            ],
        ]);
        CompetitionOrganizerType::createMany([
            [
                'name' => 'Kompetisi Non-Kemdikbud',
                'weight' => 1,
            ],
            [
                'name' => 'Kompetisi Kemdikbud (Puspresnas)',
                'weight' => 3,
            ],
        ]);
        CompetitionOutput::createMany([
            [
                'name' => 'Ide',
                'weight' => 1,
            ],
            [
                'name' => 'Pelaksanaan',
                'weight' => 2,
            ],
            [
                'name' => 'Hasil/Produk',
                'weight' => 3,
            ],
        ]);
        CompetitionRank::createMany([
            [
                'name' => 'Peserta',
                'weight' => 0,
            ],
            [
                'name' => 'Pendanaan',
                'weight' => 1,
            ],
            [
                'name' => 'Finalis/Juara Harapan',
                'weight' => 2,
            ],
            [
                'name' => 'Juara 3',
                'weight' => 3,
            ],
            [
                'name' => 'Juara 2',
                'weight' => 4,
            ],
            [
                'name' => 'Juara 1',
                'weight' => 5,
            ],
        ]);
        CompetitionScale::createMany([
            [
                'name' => 'UNY',
                'weight' => 1,
            ],
            [
                'name' => 'Kabupaten/Kota',
                'weight' => 2,
            ],
            [
                'name' => 'Daerah/Provinsi',
                'weight' => 3,
            ],
            [
                'name' => 'Wilayah',
                'weight' => 4,
            ],
            [
                'name' => 'Nasional',
                'weight' => 5,
            ],
            [
                'name' => 'Internasional',
                'weight' => 6,
            ],
        ]);
        CompetitionTimeRange::createMany([
            [
                'name' => '<3 Bulan',
                'weight' => 1,
            ],
            [
                'name' => '3-6 Bulan',
                'weight' => 2,
            ],
            [
                'name' => '>6 Bulan',
                'weight' => 3,
            ],
        ]);
        CompetitionTeamType::createMany([
            [
                'name' => 'Individual',
                'weight' => 1,
            ],
            [
                'name' => 'Beregu (2 Anggota)',
                'weight' => 2,
            ],
            [
                'name' => 'Beregu (>2 Anggota)',
                'weight' => 3,
            ],
        ]);
    }
}
