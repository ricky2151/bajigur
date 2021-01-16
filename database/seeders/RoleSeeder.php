<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::factory()->times(1)->create([
            'name' => 'user'
        ]);

        Role::factory()->times(1)->create([
            'name' => 'admin'
        ]);

        Role::factory()->times(1)->create([
            'name' => 'superadmin'
        ]);
    }
}
