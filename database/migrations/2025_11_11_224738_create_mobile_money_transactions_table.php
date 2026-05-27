<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mobile_money_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('cnib');
            $table->string('telephone');
            $table->enum('type_operation', ['depot', 'retrait']);
            $table->enum('nature', ['orange_money', 'telecel_money', 'moov_money']);
            $table->decimal('montant', 10, 2);
            $table->string('id_transaction')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->enum('statut', ['actif', 'annule'])->default('actif');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['admin_id', 'created_at']);
            $table->index('type_operation');
            $table->index('nature');
            $table->index('statut');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_money_transactions');
    }
};