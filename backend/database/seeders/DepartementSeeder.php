<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departements')->insert([
            ['DepartementID' => 1, 'RegionID' => 1, 'NomDepartement' => 'Djérem'],
            ['DepartementID' => 2, 'RegionID' => 1, 'NomDepartement' => 'Faro-et-Déo'],
            ['DepartementID' => 3, 'RegionID' => 1, 'NomDepartement' => 'Mayo-Banyo'],
            ['DepartementID' => 4, 'RegionID' => 1, 'NomDepartement' => 'Mbéré'],
            ['DepartementID' => 5, 'RegionID' => 1, 'NomDepartement' => 'Vina'],
            ['DepartementID' => 6, 'RegionID' => 2, 'NomDepartement' => 'Haute-Sanaga'],
            ['DepartementID' => 7, 'RegionID' => 2, 'NomDepartement' => 'Lekié'],
            ['DepartementID' => 8, 'RegionID' => 2, 'NomDepartement' => 'Mbam-et-Inoubou'],
            ['DepartementID' => 9, 'RegionID' => 2, 'NomDepartement' => 'Mbam-et-Kim'],
            ['DepartementID' => 10, 'RegionID' => 2, 'NomDepartement' => 'Méfou-et-Afamba'],
            ['DepartementID' => 11, 'RegionID' => 2, 'NomDepartement' => 'Méfou-et-Akono'],
            ['DepartementID' => 12, 'RegionID' => 2, 'NomDepartement' => 'Mfoundi'],
            ['DepartementID' => 13, 'RegionID' => 3, 'NomDepartement' => 'Boumba-et-Ngoko'],
            ['DepartementID' => 14, 'RegionID' => 3, 'NomDepartement' => 'Haut-Nyong'],
            ['DepartementID' => 15, 'RegionID' => 3, 'NomDepartement' => 'Kadey'],
            ['DepartementID' => 16, 'RegionID' => 3, 'NomDepartement' => 'Lom-et-Djérem'],
            ['DepartementID' => 17, 'RegionID' => 4, 'NomDepartement' => 'Diamaré'],
            ['DepartementID' => 18, 'RegionID' => 4, 'NomDepartement' => 'Logone-et-Chari'],
            ['DepartementID' => 19, 'RegionID' => 4, 'NomDepartement' => 'Mayo-Danay'],
            ['DepartementID' => 20, 'RegionID' => 4, 'NomDepartement' => 'Mayo-Kani'],
            ['DepartementID' => 21, 'RegionID' => 4, 'NomDepartement' => 'Mayo-Sava'],
            ['DepartementID' => 22, 'RegionID' => 4, 'NomDepartement' => 'Mayo-Tsanaga'],
            ['DepartementID' => 23, 'RegionID' => 5, 'NomDepartement' => 'Moungo'],
            ['DepartementID' => 24, 'RegionID' => 5, 'NomDepartement' => 'Nkam'],
            ['DepartementID' => 25, 'RegionID' => 5, 'NomDepartement' => 'Sanaga-Maritime'],
            ['DepartementID' => 26, 'RegionID' => 5, 'NomDepartement' => 'Wouri'],
            ['DepartementID' => 27, 'RegionID' => 6, 'NomDepartement' => 'Bénoué'],
            ['DepartementID' => 28, 'RegionID' => 6, 'NomDepartement' => 'Faro'],
            ['DepartementID' => 29, 'RegionID' => 6, 'NomDepartement' => 'Mayo-Louti'],
            ['DepartementID' => 30, 'RegionID' => 6, 'NomDepartement' => 'Mayo-Rey'],
            ['DepartementID' => 31, 'RegionID' => 7, 'NomDepartement' => 'Boyo'],
            ['DepartementID' => 32, 'RegionID' => 7, 'NomDepartement' => 'Bui'],
            ['DepartementID' => 33, 'RegionID' => 7, 'NomDepartement' => 'Donga-Mantung'],
            ['DepartementID' => 34, 'RegionID' => 7, 'NomDepartement' => 'Menchum'],
            ['DepartementID' => 35, 'RegionID' => 7, 'NomDepartement' => 'Mezam'],
            ['DepartementID' => 36, 'RegionID' => 7, 'NomDepartement' => 'Momo'],
            ['DepartementID' => 37, 'RegionID' => 7, 'NomDepartement' => 'Ngoketunjia'],
            ['DepartementID' => 38, 'RegionID' => 8, 'NomDepartement' => 'Bamboutos'],
            ['DepartementID' => 39, 'RegionID' => 8, 'NomDepartement' => 'Haut-Nkam'],
            ['DepartementID' => 40, 'RegionID' => 8, 'NomDepartement' => 'Hauts-Plateaux'],
            ['DepartementID' => 41, 'RegionID' => 8, 'NomDepartement' => 'Koung-Khi'],
            ['DepartementID' => 42, 'RegionID' => 8, 'NomDepartement' => 'Menoua'],
            ['DepartementID' => 43, 'RegionID' => 8, 'NomDepartement' => 'Mifi'],
            ['DepartementID' => 44, 'RegionID' => 8, 'NomDepartement' => 'Ndé'],
            ['DepartementID' => 45, 'RegionID' => 8, 'NomDepartement' => 'Noun'],
            ['DepartementID' => 46, 'RegionID' => 9, 'NomDepartement' => 'Dja-et-Lobo'],
            ['DepartementID' => 47, 'RegionID' => 9, 'NomDepartement' => 'Mvila'],
            ['DepartementID' => 48, 'RegionID' => 9, 'NomDepartement' => 'Océan'],
            ['DepartementID' => 49, 'RegionID' => 9, 'NomDepartement' => 'Vallée-du-Ntem'],
            ['DepartementID' => 50, 'RegionID' => 10, 'NomDepartement' => 'Fako'],
            ['DepartementID' => 51, 'RegionID' => 10, 'NomDepartement' => 'Koupé-Manengouba'],
            ['DepartementID' => 52, 'RegionID' => 10, 'NomDepartement' => 'Lebialem'],
            ['DepartementID' => 53, 'RegionID' => 10, 'NomDepartement' => 'Manyu'],
            ['DepartementID' => 54, 'RegionID' => 10, 'NomDepartement' => 'Meme'],
            ['DepartementID' => 55, 'RegionID' => 10, 'NomDepartement' => 'Ndian'],
            ['DepartementID' => 56, 'RegionID' => 2, 'NomDepartement' => 'Nyong-et-Kéllé'],
            ['DepartementID' => 57, 'RegionID' => 2, 'NomDepartement' => 'Nyong-et-Mfoumou'],
            ['DepartementID' => 58, 'RegionID' => 2, 'NomDepartement' => "Nyong-et-So'o"],
        ]);
    }
}
