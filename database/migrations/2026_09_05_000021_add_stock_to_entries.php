<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            // null = sin control de stock (ilimitado). 0 = agotado.
            $table->unsignedInteger('stock')->nullable()->after('data');
        });
    }

    public function down(): void
    {
        Schema::table('entries', fn (Blueprint $table) => $table->dropColumn('stock'));
    }
};
