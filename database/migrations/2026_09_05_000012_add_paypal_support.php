<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('paypal_enabled')->default(false)->after('currency');
            $table->string('paypal_mode')->default('sandbox')->after('paypal_enabled'); // sandbox | live
            $table->string('paypal_client_id')->nullable()->after('paypal_mode');
            $table->text('paypal_secret')->nullable()->after('paypal_client_id'); // encriptado
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('reference'); // stripe | paypal
            $table->string('paypal_order_id')->nullable()->after('stripe_payment_intent');
            $table->string('paypal_capture_id')->nullable()->after('paypal_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['paypal_enabled', 'paypal_mode', 'paypal_client_id', 'paypal_secret']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['provider', 'paypal_order_id', 'paypal_capture_id']);
        });
    }
};
