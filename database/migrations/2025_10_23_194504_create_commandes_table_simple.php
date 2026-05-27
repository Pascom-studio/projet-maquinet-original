<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_commande')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // caissier
            $table->foreignId('hotesse_id')->constrained('users')->onDelete('cascade'); // hôtesse
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->decimal('montant_total', 10, 2)->default(0);
            $table->enum('statut', ['en cours', 'soldée', 'annulée'])->default('en cours');
            $table->text('notes')->nullable();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Index
            $table->index(['user_id', 'hotesse_id', 'table_id']);
            $table->index(['statut', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('commandes');
    }
};