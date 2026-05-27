<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_theme_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('navbar_bg')->default('0b5f37');
            $table->string('footer_bg')->default('0b5f37');
            $table->string('primary_text')->default('ffffff');
            $table->string('hover_color')->default('ee8f13');
            $table->timestamps();
            
            $table->unique('user_id'); // Un thème par utilisateur
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_theme_settings');
    }
};