<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('caisses', function (Blueprint $table) {
            $table->decimal('total_commandes_soldees', 10, 2)->default(0)->after('total_depenses');
        });
    }

    public function down()
    {
        Schema::table('caisses', function (Blueprint $table) {
            $table->dropColumn('total_commandes_soldees');
        });
    }
};