<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            $table->enum('nature', ['orange_money', 'telecel_money', 'moov_money', 'coris_money'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            $table->enum('nature', ['orange_money', 'telecel_money', 'moov_money'])->change();
        });
    }
};