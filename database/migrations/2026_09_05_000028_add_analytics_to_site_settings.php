<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->text('analytics_head')->nullable();   // código para el <head> (GA4, Meta Pixel…)
            $table->text('analytics_body')->nullable();   // código antes de </body>
            $table->boolean('cookie_banner')->default(false);
            $table->string('cookie_text')->nullable();
            $table->string('cookie_policy_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['analytics_head', 'analytics_body', 'cookie_banner', 'cookie_text', 'cookie_policy_url']);
        });
    }
};
