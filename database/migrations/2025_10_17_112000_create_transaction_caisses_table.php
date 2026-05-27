<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transaction_caisses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caisse_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['retrait', 'approvisionnement', 'vente']);
            $table->decimal('montant', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            // Index pour les performances
            $table->index(['caisse_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_caisses');
    }
};