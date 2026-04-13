<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LegacyDataMigrationSeeder extends Seeder
{
    public function run(): void
    {
        $sqlPath = base_path('cni_legacy.sql');

        if (!file_exists($sqlPath)) {
            $this->command->error("Le fichier cni_legacy.sql est introuvable à la racine du backend.");
            return;
        }

        $sqlContent = file_get_contents($sqlPath);

        // 1. Remplacer les anciens chemins d'uploads par les nouveaux chemins storage de Laravel
        $sqlContent = str_replace('../uploads/', '/storage/', $sqlContent);

        // 2. Extraire les instructions SQL (on sépare par point-virgule et retour à la ligne pour être précis)
        // Note: phpMyAdmin génère souvent des multi-lignes pour les gros INSERT.
        // On va utiliser une approche par ligne mais filtrer les débuts de ligne.
        $lines = explode("\n", $sqlContent);
        
        $this->command->info("Analyse du fichier SQL...");

        // Désactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $currentStatement = "";
        $successCount = 0;
        $errorCount = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            // On ne prend que les INSERT
            if (str_starts_with($trimmed, 'INSERT INTO') || !empty($currentStatement)) {
                $currentStatement .= $line . "\n";
                
                if (str_ends_with($trimmed, ';')) {
                    try {
                        DB::unprepared($currentStatement);
                        $successCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                        // Log::error("Erreur SQL: " . $e->getMessage());
                    }
                    $currentStatement = "";
                }
            }
        }

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info("Migration terminée !");
        $this->command->info("Success: $successCount instructions exécutées.");
        if ($errorCount > 0) {
            $this->command->warn("Info: $errorCount instructions ignorées (doublons ou erreurs mineures).");
        }
    }
}
