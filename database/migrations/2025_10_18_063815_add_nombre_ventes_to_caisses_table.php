<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('caisses', function (Blueprint $table) {
            if (!Schema::hasColumn('caisses', 'nombre_ventes')) {
                $table->integer('nombre_ventes')->default(0)->after('total_ventes');
            }
        });
    }

    public function down()
    {
        Schema::table('caisses', function (Blueprint $table) {
            $table->dropColumn('nombre_ventes');
        });
    }
};