<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            $table->decimal('commission_brute', 10, 2)->default(0)->after('commission');
            $table->decimal('taxes', 10, 2)->default(0)->after('commission_brute');
        });

        Schema::table('mobile_money_commissions', function (Blueprint $table) {
            $table->decimal('commission_nette', 10, 2)->default(0)->after('commission_total');
            $table->decimal('taxes_total', 10, 2)->default(0)->after('commission_nette');
        });
    }

    public function down()
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            $table->dropColumn(['commission_brute', 'taxes']);
        });

        Schema::table('mobile_money_commissions', function (Blueprint $table) {
            $table->dropColumn(['commission_nette', 'taxes_total']);
        });
    }
};

