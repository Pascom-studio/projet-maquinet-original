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
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            // Supprimer TOUTES les colonnes liées à la signature
            $columnsToDrop = [
                'signature',
                'has_signature', 
                'signature_url',
                'format_signature',
                'signature_data',
                'signature_format'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('mobile_money_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            // Recréer les colonnes si rollback (optionnel)
            $table->text('signature')->nullable()->after('id_transaction');
            $table->boolean('has_signature')->default(false)->after('signature');
            $table->string('signature_url')->nullable()->after('has_signature');
            $table->string('format_signature')->nullable()->after('signature_url');
        });
    }
};