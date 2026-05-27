<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('caisses', function (Blueprint $table) {
        $table->decimal('total_depenses', 15, 2)->default(0)->after('total_retraits');
    });
}

public function down()
{
    Schema::table('caisses', function (Blueprint $table) {
        $table->dropColumn('total_depenses');
    });
}
};
