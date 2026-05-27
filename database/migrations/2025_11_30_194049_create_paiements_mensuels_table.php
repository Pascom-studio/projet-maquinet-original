<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaiementsMensuelsTable extends Migration
{
    public function up()
    {
        Schema::create('paiements_mensuels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('annee');
            $table->integer('mois');
            $table->decimal('montant', 10, 2)->default(0);
            $table->boolean('est_paye')->default(false);
            $table->timestamp('date_paiement')->nullable();
            $table->foreignId('paye_par')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->unique(['user_id', 'annee', 'mois']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('paiements_mensuels');
    }
}