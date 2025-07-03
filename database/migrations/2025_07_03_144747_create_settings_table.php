<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->longText('logo')->nullable();
            $table->longText('auth_logo')->nullable();
            $table->longText('mobile_logo')->nullable();
            $table->longText('favicon')->nullable();
            $table->string('app_name')->nullable();
            $table->string('whatsApp_number')->nullable();
            $table->string('missed_call_number')->nullable();
            $table->boolean('user_notification_permission')->nullable();
            $table->boolean('welcome_modal_status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
