<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mobile_money_liquidities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('montant', 10, 2)->default(0);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_money_liquidities');
    }
};