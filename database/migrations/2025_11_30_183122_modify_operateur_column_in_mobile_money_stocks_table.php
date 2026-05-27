<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyOperateurColumnInMobileMoneyStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mobile_money_stocks', function (Blueprint $table) {
            // Modifier la colonne operateur pour accepter des valeurs plus longues
            $table->string('operateur', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mobile_money_stocks', function (Blueprint $table) {
            // Revenir à la longueur précédente si nécessaire
            $table->string('operateur', 20)->change();
        });
    }
}