<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('caisses', function (Blueprint $table) {
            // Ajouter les colonnes manquantes
            if (!Schema::hasColumn('caisses', 'solde_actuel')) {
                $table->decimal('solde_actuel', 15, 2)->default(0)->after('solde_ouverture');
            }
            
            if (!Schema::hasColumn('caisses', 'total_ventes')) {
                $table->decimal('total_ventes', 15, 2)->default(0)->after('solde_actuel');
            }
            
            if (!Schema::hasColumn('caisses', 'total_approvisionnements')) {
                $table->decimal('total_approvisionnements', 15, 2)->default(0)->after('total_ventes');
            }
            
            if (!Schema::hasColumn('caisses', 'total_retraits')) {
                $table->decimal('total_retraits', 15, 2)->default(0)->after('total_approvisionnements');
            }
            
            if (!Schema::hasColumn('caisses', 'date_ouverture')) {
                $table->timestamp('date_ouverture')->nullable()->after('total_retraits');
            }
            
            if (!Schema::hasColumn('caisses', 'date_fermeture')) {
                $table->timestamp('date_fermeture')->nullable()->after('date_ouverture');
            }
        });
    }

    public function down()
    {
        Schema::table('caisses', function (Blueprint $table) {
            $table->dropColumn([
                'solde_actuel',
                'total_ventes', 
                'total_approvisionnements',
                'total_retraits',
                'date_ouverture',
                'date_fermeture'
            ]);
        });
    }
};