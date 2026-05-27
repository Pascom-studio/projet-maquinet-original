<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MigrateExistingMobileCaissiers extends Command
{
    protected $signature = 'migrate:mobile-caissiers';
    protected $description = 'Migrer les mobile caissiers existants vers le nouveau système';

    public function handle()
    {
        $this->info('Migration des mobile caissiers existants...');

        // Récupérer tous les mobile caissiers existants
        $mobileCaissiers = User::where('fonction', 'mobile_caissier')->get();

        foreach ($mobileCaissiers as $caissier) {
            // Ici, vous pouvez assigner des valeurs par défaut
            // Par exemple, les marquer tous comme actifs
            $caissier->est_actif = true;
            $caissier->save();

            $this->info("Migré: {$caissier->prenom} {$caissier->name}");
        }

        $this->info('Migration terminée!');
        return 0;
    }
}