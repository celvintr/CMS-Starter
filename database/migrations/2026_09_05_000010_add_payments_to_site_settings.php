<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('stripe_enabled')->default(false)->after('ai_model');
            $table->string('stripe_public_key')->nullable()->after('stripe_enabled');
            $table->text('stripe_secret_key')->nullable()->after('stripe_public_key');       // encriptada
            $table->text('stripe_webhook_secret')->nullable()->after('stripe_secret_key');    // encriptada
            $table->string('currency', 3)->default('usd')->after('stripe_webhook_secret');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['stripe_enabled', 'stripe_public_key', 'stripe_secret_key', 'stripe_webhook_secret', 'currency']);
        });
    }
};
