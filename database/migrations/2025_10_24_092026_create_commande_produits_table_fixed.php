<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Vérifier si la table n'existe pas déjà
        if (!Schema::hasTable('commande_produits')) {
            Schema::create('commande_produits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('product')->onDelete('cascade');
                $table->integer('quantite');
                $table->decimal('prix_unitaire', 10, 2);
                $table->decimal('prix_total', 10, 2);
                $table->timestamps();

                // Index
                $table->index(['commande_id', 'product_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('commande_produits');
    }
};