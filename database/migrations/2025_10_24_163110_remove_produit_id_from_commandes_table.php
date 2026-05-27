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
    Schema::table('commandes', function (Blueprint $table) {
        $table->dropForeign(['produit_id']);
        $table->dropColumn('produit_id');
    });
}

public function down()
{
    Schema::table('commandes', function (Blueprint $table) {
        $table->foreignId('produit_id')->constrained()->after('table_id');
    });
}
};
