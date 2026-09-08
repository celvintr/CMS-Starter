<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->json('reservation_days')->nullable();               // [1..7] ISO (1=lunes)
            $table->string('reservation_open', 5)->nullable();          // "09:00"
            $table->string('reservation_close', 5)->nullable();         // "17:00"
            $table->unsignedInteger('reservation_slot_minutes')->default(30);
            $table->unsignedInteger('reservation_capacity')->default(1); // reservas por horario
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['reservation_days', 'reservation_open', 'reservation_close', 'reservation_slot_minutes', 'reservation_capacity']);
        });
    }
};
