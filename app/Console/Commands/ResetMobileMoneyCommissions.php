<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\MobileMoneyController;

class ResetMobileMoneyCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commissions:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remise à zéro des commissions Mobile Money';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Début de la remise à zéro des commissions...');
        
        $controller = new MobileMoneyController();
        $result = $controller->resetCommissions();
        
        if ($result) {
            $this->info('✅ Vérification des commissions effectuée avec succès');
            $this->info('📊 Les commissions du mois ' . now()->format('F Y') . ' sont prêtes');
        } else {
            $this->error('❌ Erreur lors de la vérification des commissions');
        }
    }
}