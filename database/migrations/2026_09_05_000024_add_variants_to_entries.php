<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            // [{label, price, stock}] — si hay variantes, precio y stock salen de aquí.
            $table->json('variants')->nullable()->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('entries', fn (Blueprint $table) => $table->dropColumn('variants'));
    }
};
