<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Supprimer l'ancienne contrainte d'unicité globale
        Schema::table('tables', function (Blueprint $table) {
            // Supprimer l'index unique existant
            $table->dropUnique(['numero']);
        });

        // Ajouter une nouvelle contrainte d'unicité combinée (numero + admin_id)
        Schema::table('tables', function (Blueprint $table) {
            $table->unique(['numero', 'admin_id']);
        });
    }

    public function down()
    {
        // Revenir à l'ancienne structure
        Schema::table('tables', function (Blueprint $table) {
            $table->dropUnique(['numero', 'admin_id']);
            $table->unique(['numero']);
        });
    }
};