<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('singular_label')->nullable();
            $table->string('plural_label')->nullable();
            $table->string('icon')->default('heroicon-o-rectangle-stack');
            $table->boolean('is_public')->default(false);   // ¿tiene listado/página en el frontend?
            $table->json('fields')->nullable();              // definición de campos del módulo
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
