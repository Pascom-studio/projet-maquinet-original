<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // D'abord, mettons à jour les super_admin temporairement
        DB::table('users')
            ->where('fonction', 'super_admin')
            ->update(['fonction' => 'admin']);

        // Maintenant modifions la colonne avec le bon enum
        Schema::table('users', function (Blueprint $table) {
            $table->enum('fonction', ['caissier', 'gerant', 'admin', 'super_admin'])
                  ->default('caissier')
                  ->change();
        });

        // Remettons les super_admin à leur valeur correcte
        // (Vous devrez identifier vos super_admin par email ou ID)
        DB::table('users')
            ->whereIn('email', ['superadmin@gestcool.com'])
            ->update(['fonction' => 'super_admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dans le down, on ne peut pas facilement revenir en arrière
        // donc on laisse vide ou on fait l'inverse si nécessaire
    }
};