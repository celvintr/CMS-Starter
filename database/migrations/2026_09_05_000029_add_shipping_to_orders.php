<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping', 10, 2)->default(0)->after('discount');
            $table->text('shipping_address')->nullable()->after('shipping');
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('shipping_enabled')->default(false);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('shipping_free_from', 10, 2)->nullable(); // envío gratis desde este subtotal
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['shipping', 'shipping_address']));
        Schema::table('site_settings', fn (Blueprint $table) => $table->dropColumn(['shipping_enabled', 'shipping_cost', 'shipping_free_from']));
    }
};
