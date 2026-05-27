<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('caisses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('statut', ['ouverte', 'fermee'])->default('fermee');
            $table->decimal('solde_ouverture', 10, 2)->default(0);
            $table->decimal('solde_fermeture', 10, 2)->default(0);
            $table->decimal('total_ventes', 10, 2)->default(0);
            $table->decimal('total_retraits', 10, 2)->default(0);
            $table->decimal('total_approvisionnements', 10, 2)->default(0);
            $table->timestamp('date_ouverture')->nullable();
            $table->timestamp('date_fermeture')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('caisses');
    }
};