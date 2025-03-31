<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'Administrator',
            ],
            [
                'name' => 'Core',
            ],
            [
                'name' => 'Supercore',
            ],
            [
                'name' => 'Member',
            ],
        ];

        foreach ($groups as $group) {
            Group::firstOrCreate($group);
        }

        $adminGroup = Group::where('name', 'Administrator')->first();

        $adminGroup->syncPermissions(Permission::all());
    }
}
