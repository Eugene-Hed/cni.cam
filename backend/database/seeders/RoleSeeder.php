<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('role')->insert([
            ['id' => 1, 'role' => 'Administrateur'],
            ['id' => 2, 'role' => 'Citoyen'],
            ['id' => 3, 'role' => 'Officier'],
            ['id' => 4, 'role' => 'President'],
        ]);
    }
}
