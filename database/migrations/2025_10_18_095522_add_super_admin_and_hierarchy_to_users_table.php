<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter le rôle super_admin
            $table->enum('fonction', ['caissier', 'gerant', 'admin', 'super_admin'])->default('caissier')->change();
            
            // Ajouter la hiérarchie (admin_id pour regrouper les opérations)
            $table->foreignId('admin_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            
            // Index pour les performances
            $table->index(['admin_id', 'fonction']);
            $table->index('fonction');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
            $table->enum('fonction', ['caissier', 'gerant', 'admin'])->default('caissier')->change();
            $table->dropIndex(['admin_id', 'fonction']);
            $table->dropIndex(['fonction']);
        });
    }
};