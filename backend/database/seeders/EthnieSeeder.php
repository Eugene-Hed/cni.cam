<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EthnieSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ethnies')->insert([
            ['EthnieID' => 1, 'NomEthnie' => 'Bassa', 'Description' => 'Le peuple Bassa est originaire de la région du Littoral.', 'RegionPrincipale' => 5],
            ['EthnieID' => 2, 'NomEthnie' => 'Bamiléké', 'Description' => 'Les Bamilékés sont une ethnie du Cameroun située dans la région de l\'Ouest.', 'RegionPrincipale' => 8],
            ['EthnieID' => 3, 'NomEthnie' => 'Beti', 'Description' => 'Le peuple Beti est originaire du Centre du Cameroun.', 'RegionPrincipale' => 2],
            ['EthnieID' => 4, 'NomEthnie' => 'Douala', 'Description' => 'Le peuple Douala est un groupe ethnique du Littoral.', 'RegionPrincipale' => 5],
            ['EthnieID' => 5, 'NomEthnie' => 'Fang', 'Description' => 'Le peuple Fang vit principalement dans la région du Sud.', 'RegionPrincipale' => 9],
            ['EthnieID' => 6, 'NomEthnie' => 'Moungo', 'Description' => 'Les Moungos sont originaires de la région du Littoral.', 'RegionPrincipale' => 5],
            ['EthnieID' => 7, 'NomEthnie' => 'Maka', 'Description' => 'Les Maka sont une ethnie du Centre.', 'RegionPrincipale' => 2],
            ['EthnieID' => 8, 'NomEthnie' => 'Mouko', 'Description' => 'Les Moukos vivent dans la région de l\'Ouest.', 'RegionPrincipale' => 8],
            ['EthnieID' => 9, 'NomEthnie' => 'Pygmées', 'Description' => 'Les Pygmées sont un groupe ethnique vivant dans les forêts équatoriales.', 'RegionPrincipale' => 3],
            ['EthnieID' => 10, 'NomEthnie' => 'Tikar', 'Description' => 'Les Tikars sont un peuple de la région du Centre et de l\'Ouest.', 'RegionPrincipale' => 8],
            ['EthnieID' => 11, 'NomEthnie' => 'Beti-Ewondo', 'Description' => 'Le peuple Ewondo se trouve principalement dans le Centre.', 'RegionPrincipale' => 2],
            ['EthnieID' => 12, 'NomEthnie' => 'Mashi', 'Description' => 'Le peuple Mashi est situé dans le Nord-Ouest.', 'RegionPrincipale' => 7],
            ['EthnieID' => 13, 'NomEthnie' => 'Ngumba', 'Description' => 'Les Ngumba sont un groupe ethnique du Sud-Ouest.', 'RegionPrincipale' => 10],
            ['EthnieID' => 14, 'NomEthnie' => 'Sawa', 'Description' => 'Les Sawa sont principalement dans les régions côtières.', 'RegionPrincipale' => 5],
            ['EthnieID' => 15, 'NomEthnie' => 'Fulbé', 'Description' => 'Le peuple Fulbé ou Peulh est présent dans le Nord.', 'RegionPrincipale' => 6],
            ['EthnieID' => 16, 'NomEthnie' => 'Bakossi', 'Description' => 'Les Bakossi sont originaires du Sud-Ouest.', 'RegionPrincipale' => 10],
        ]);
    }
}
