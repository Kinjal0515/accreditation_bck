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
        Schema::create('whatsapp_apis', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('title')->nullable();
            $table->longText('variables')->nullable();
            $table->longText('template_name')->nullable();
            $table->string('url')->nullable();
            $table->boolean('custom')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_apis');
    }
};
