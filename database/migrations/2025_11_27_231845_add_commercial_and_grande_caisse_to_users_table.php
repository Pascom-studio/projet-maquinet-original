<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommercialAndGrandeCaisseToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Pour lier les mobile caissiers aux commerciaux
            $table->foreignId('commercial_id')->nullable()->constrained('users');
            
            // Pour regrouper les comptes dans la grande caisse mobile
            $table->foreignId('grande_caisse_id')->nullable()->constrained('users');
            
            // Pour le statut actif/inactif des mobile caissiers
            $table->boolean('est_actif')->default(true);
            
            // Pour stocker les paiements mensuels
            $table->json('paiements_mensuels')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropForeign(['grande_caisse_id']);
            $table->dropColumn(['commercial_id', 'grande_caisse_id', 'est_actif', 'paiements_mensuels']);
        });
    }
}