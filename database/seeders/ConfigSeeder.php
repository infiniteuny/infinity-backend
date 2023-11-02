<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Config::create([
            'key' => 'competition_target',
            'value' => '25',
            'type' => 'integer',
        ]);
        Config::create([
            'key' => 're_registration',
            'value' => 'true',
            'type' => 'boolean',
        ]);
        Config::create([
            'key' => 'freepik_limit',
            'value' => '0',
            'type' => 'string',
        ]);
    }
}
