<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Guard;

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
                'name' => 'Member',
                'is_managed' => true,
            ],
            [
                'name' => 'Active Member',
                'is_managed' => true,
            ],
            [
                'name' => 'Inactive Member',
                'is_managed' => true,
            ],
            [
                'name' => 'Core Team',
                'is_managed' => true,
            ],
            [
                'name' => 'Ex Core Team',
                'is_managed' => true,
            ],
            [
                'name' => 'CG Admin',
                'is_managed' => true,
            ],
            [
                'name' => 'Ex CG Admin',
                'is_managed' => true,
            ],
        ];

        $guardName = Guard::getDefaultName(User::class);

        foreach ($groups as $group) {
            Group::updateOrCreate(
                [
                    'name' => $group['name'],
                    'guard_name' => $guardName,
                ],
                [
                    'is_managed' => $group['is_managed'],
                ],
            );
        }

        $adminGroup = Group::where('name', 'Administrator')->first();

        $adminGroup->syncPermissions(Permission::all());
    }
}
