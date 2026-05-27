<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // created, updated, deleted, restored
            $table->string('table_name'); // ventes, ventes_multiples, mouvements, etc.
            $table->unsignedBigInteger('record_id');
            $table->text('old_data')->nullable();
            $table->text('new_data')->nullable();
            $table->text('description');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Index pour les performances
            $table->index(['table_name', 'record_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audits');
    }
};