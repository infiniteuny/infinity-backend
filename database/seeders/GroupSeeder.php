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
                'is_managed' => true,
            ],
            [
                'name' => 'Core',
                'is_managed' => true,
            ],
            [
                'name' => 'Supercore',
                'is_managed' => true,
            ],
            [
                'name' => 'Member',
                'is_managed' => true,
            ],
        ];

        foreach ($groups as $group) {
            Group::firstOrCreate($group);
        }

        $adminGroup = Group::where('name', 'Administrator')->first();

        $adminGroup->syncPermissions(Permission::all());
    }
}
