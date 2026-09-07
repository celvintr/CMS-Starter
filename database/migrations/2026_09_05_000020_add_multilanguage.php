<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('default_language', 5)->default('es')->after('features');
            $table->json('languages')->nullable()->after('default_language');   // [{code,name}]
            $table->json('translations')->nullable()->after('languages');       // {locale: {campo: valor}}
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('content');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['default_language', 'languages', 'translations']);
        });
        Schema::table('pages', fn (Blueprint $t) => $t->dropColumn('translations'));
        Schema::table('posts', fn (Blueprint $t) => $t->dropColumn('translations'));
    }
};
