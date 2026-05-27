<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Recréer la table ventes proprement
        Schema::dropIfExists('ventes');
        
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('caisse_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('montant_total', 10, 2);
            $table->string('numero_vente')->unique();
            $table->decimal('cash_client', 10, 2);
            $table->decimal('monnaie_rendue', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ventes');
    }
};