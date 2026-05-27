<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddAvoirDepenseToMobileMoneyStocks extends Migration
{
    public function up()
    {
        // Méthode pour modifier un ENUM
        DB::statement("ALTER TABLE mobile_money_stocks MODIFY COLUMN type_mouvement ENUM('approvisionnement', 'remboursement', 'avoir', 'depense') NOT NULL");
    }

    public function down()
    {
        // Revenir aux valeurs originales
        DB::statement("ALTER TABLE mobile_money_stocks MODIFY COLUMN type_mouvement ENUM('approvisionnement', 'remboursement') NOT NULL");
    }
}