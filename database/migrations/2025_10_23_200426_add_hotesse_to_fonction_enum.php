<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Modifier la colonne fonction pour inclure 'hotesse'
        DB::statement("ALTER TABLE users MODIFY fonction ENUM('super_admin', 'admin', 'gerant', 'caissier', 'hotesse') NOT NULL");
    }

    public function down()
    {
        // Revenir à l'ancien enum
        DB::statement("ALTER TABLE users MODIFY fonction ENUM('super_admin', 'admin', 'gerant', 'caissier') NOT NULL");
    }
};