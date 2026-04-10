<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('regions')->insert([
            ['RegionID' => 1, 'NomRegion' => 'Adamaoua'],
            ['RegionID' => 2, 'NomRegion' => 'Centre'],
            ['RegionID' => 3, 'NomRegion' => 'Est'],
            ['RegionID' => 4, 'NomRegion' => 'Extrême-Nord'],
            ['RegionID' => 5, 'NomRegion' => 'Littoral'],
            ['RegionID' => 6, 'NomRegion' => 'Nord'],
            ['RegionID' => 7, 'NomRegion' => 'Nord-Ouest'],
            ['RegionID' => 8, 'NomRegion' => 'Ouest'],
            ['RegionID' => 9, 'NomRegion' => 'Sud'],
            ['RegionID' => 10, 'NomRegion' => 'Sud-Ouest'],
        ]);
    }
}
