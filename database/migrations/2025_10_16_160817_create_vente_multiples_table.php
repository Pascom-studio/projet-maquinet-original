<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vente_multiples', function (Blueprint $table) {
            $table->id();
            $table->string('numero_vente')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('caisse_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('montant_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vente_multiples');
    }
};