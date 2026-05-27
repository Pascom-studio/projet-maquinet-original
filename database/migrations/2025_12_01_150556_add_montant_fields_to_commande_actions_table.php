<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('commande_actions', function (Blueprint $table) {
            // Ajouter les colonnes si elles n'existent pas
            if (!Schema::hasColumn('commande_actions', 'montant_commande')) {
                $table->decimal('montant_commande', 10, 2)->default(0)->nullable()->after('details');
            }
            
            if (!Schema::hasColumn('commande_actions', 'montant_remis')) {
                $table->decimal('montant_remis', 10, 2)->default(0)->nullable()->after('montant_commande');
            }
            
            if (!Schema::hasColumn('commande_actions', 'montant_restant')) {
                $table->decimal('montant_restant', 10, 2)->default(0)->nullable()->after('montant_remis');
            }
            
            if (!Schema::hasColumn('commande_actions', 'methode_paiement')) {
                $table->string('methode_paiement', 50)->nullable()->after('montant_restant');
            }
        });
        
        // Migrer les données JSON vers les nouvelles colonnes pour les actions de soldage
        DB::statement("
            UPDATE commande_actions 
            SET 
                montant_commande = CASE 
                    WHEN JSON_VALID(details) 
                    THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(details, '$.montant_commande')) AS DECIMAL(10,2))
                    ELSE 0 
                END,
                montant_remis = CASE 
                    WHEN JSON_VALID(details) 
                    THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(details, '$.montant_remis')) AS DECIMAL(10,2))
                    ELSE 0 
                END,
                montant_restant = CASE 
                    WHEN JSON_VALID(details) 
                    THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(details, '$.montant_commande')) AS DECIMAL(10,2)) 
                         - CAST(JSON_UNQUOTE(JSON_EXTRACT(details, '$.montant_remis')) AS DECIMAL(10,2))
                    ELSE 0 
                END,
                methode_paiement = CASE 
                    WHEN JSON_VALID(details) 
                    THEN JSON_UNQUOTE(JSON_EXTRACT(details, '$.methode_paiement'))
                    ELSE NULL 
                END
            WHERE action = 'solder'
        ");
    }

    public function down()
    {
        Schema::table('commande_actions', function (Blueprint $table) {
            $table->dropColumn([
                'montant_commande',
                'montant_remis', 
                'montant_restant',
                'methode_paiement'
            ]);
        });
    }
};