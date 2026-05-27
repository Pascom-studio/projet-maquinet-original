<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ligne_ventes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('montant_total', 10, 2);
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('vente_id');
            $table->index('product_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ligne_ventes');
    }
};