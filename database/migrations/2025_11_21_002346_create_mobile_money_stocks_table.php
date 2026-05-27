<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mobile_money_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->enum('operateur', ['orange_money', 'telecel_money', 'moov_money', 'coris_money']);
            $table->enum('type_mouvement', ['approvisionnement', 'remboursement']);
            $table->decimal('montant', 12, 2);
            $table->string('reference');
            $table->text('notes')->nullable();
            $table->enum('statut', ['en_attente', 'termine', 'annule'])->default('termine');
            $table->timestamps();

            // Index
            $table->index(['user_id', 'operateur']);
            $table->index(['created_at']);
            $table->index(['type_mouvement']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_money_stocks');
    }
};