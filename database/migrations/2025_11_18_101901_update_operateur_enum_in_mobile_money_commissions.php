<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'enum pour ajouter coris_money
        DB::statement("ALTER TABLE mobile_money_commissions MODIFY operateur ENUM('orange_money', 'telecel_money', 'moov_money', 'coris_money') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancien enum (sans coris_money)
        DB::statement("ALTER TABLE mobile_money_commissions MODIFY operateur ENUM('orange_money', 'telecel_money', 'moov_money') NOT NULL");
    }
};