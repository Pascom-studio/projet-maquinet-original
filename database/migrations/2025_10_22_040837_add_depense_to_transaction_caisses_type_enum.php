<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDepenseToTransactionCaissesTypeEnum extends Migration
{
    public function up()
    {
        // Ajouter 'depense' à l'ENUM existant
        DB::statement("ALTER TABLE transaction_caisses MODIFY COLUMN type ENUM('retrait','approvisionnement','vente','depense') NOT NULL");
    }

    public function down()
    {
        // Retirer 'depense' de l'ENUM si on fait un rollback
        DB::statement("ALTER TABLE transaction_caisses MODIFY COLUMN type ENUM('retrait','approvisionnement','vente') NOT NULL");
    }
}