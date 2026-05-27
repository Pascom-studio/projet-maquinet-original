<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSignatureFormatFromMobileMoneyTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            $table->dropColumn('signature_format');
        });
    }

    public function down()
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            $table->string('signature_format')->nullable()->after('signature');
        });
    }
}