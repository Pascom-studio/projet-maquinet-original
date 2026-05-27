<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObservationsTable extends Migration
{
    public function up()
    {
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hotesse_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->text('contenu');
            $table->enum('type', ['positif', 'negatif', 'suggestion']);
            $table->enum('priorite', ['faible', 'moyenne', 'elevee']);
            $table->timestamp('date_observation');
            $table->boolean('est_lu')->default(false);
            $table->timestamps();

            $table->index(['hotesse_id', 'date_observation']);
            $table->index(['manager_id', 'date_observation']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('observations');
    }
}