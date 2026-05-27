<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mouvements', function (Blueprint $table) {
            // Rendre prix nullable temporairement
            $table->decimal('prix', 10, 2)->nullable()->change();
            
            // Ajouter les nouveaux champs
            $table->integer('ancien_stock')->default(0)->after('prix');
            $table->integer('nouveau_stock')->default(0)->after('ancien_stock');
            $table->string('raison')->nullable()->after('nouveau_stock');
            
            // Ajouter un index pour les performances
            $table->index(['product_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down()
    {
        Schema::table('mouvements', function (Blueprint $table) {
            $table->decimal('prix', 10, 2)->nullable(false)->change();
            $table->dropColumn(['ancien_stock', 'nouveau_stock', 'raison']);
            $table->dropIndex(['product_id', 'created_at']);
            $table->dropIndex(['type', 'created_at']);
        });
    }
};