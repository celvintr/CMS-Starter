<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->unsignedInteger('port')->default(587);
            $table->string('encryption')->nullable(); // tls | ssl | null
            $table->string('username')->nullable();
            $table->text('password')->nullable();      // encriptada
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->timestamps();
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('notify_forms_account_id')->nullable()->after('currency');
            $table->string('notify_forms_email')->nullable()->after('notify_forms_account_id');
            $table->unsignedBigInteger('notify_orders_account_id')->nullable()->after('notify_forms_email');
            $table->string('notify_orders_email')->nullable()->after('notify_orders_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_accounts');
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['notify_forms_account_id', 'notify_forms_email', 'notify_orders_account_id', 'notify_orders_email']);
        });
    }
};
