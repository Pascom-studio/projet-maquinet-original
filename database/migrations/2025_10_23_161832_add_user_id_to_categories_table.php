<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            // Ajouter la colonne user_id
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            
            // Ajouter un index pour améliorer les performances
            $table->index('user_id');
        });

        // Mettre à jour les catégories existantes avec l'admin principal
        if (Schema::hasTable('users')) {
            $admin = DB::table('users')->where('fonction', 'admin')->first();
            if ($admin) {
                DB::table('categories')->update(['user_id' => $admin->id]);
            }
        }
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};