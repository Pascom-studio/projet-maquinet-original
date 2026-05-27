<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mobile_money_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->enum('operateur', ['orange_money', 'telecel_money', 'moov_money','coris_money']);
            $table->string('mois'); // Format: Y-m (2024-11)
            $table->decimal('commission_total', 10, 2)->default(0);
            $table->integer('nombre_transactions')->default(0);
            $table->timestamps();

            // Index pour éviter les doublons
            $table->unique(['admin_id', 'operateur', 'mois']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_money_commissions');
    }
};