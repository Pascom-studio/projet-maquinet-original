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
            // Vérifier et supprimer les colonnes liées à la signature
            if (Schema::hasColumn('mobile_money_transactions', 'signature')) {
                $table->dropColumn('signature');
            }
            if (Schema::hasColumn('mobile_money_transactions', 'has_signature')) {
                $table->dropColumn('has_signature');
            }
            if (Schema::hasColumn('mobile_money_transactions', 'signature_url')) {
                $table->dropColumn('signature_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            // Recréer les colonnes si on fait un rollback (optionnel)
            $table->text('signature')->nullable()->after('id_transaction');
            $table->boolean('has_signature')->default(false)->after('signature');
            $table->string('signature_url')->nullable()->after('has_signature');
        });
    }
};