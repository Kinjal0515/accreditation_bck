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
        Schema::table('users', function (Blueprint $table) {
            $table->string('number')->after('email')->nullable();
            $table->boolean('status')->after('password')->nullable();
            $table->string('reporting_user')->after('password')->nullable();
            $table->longText('address')->after('status')->nullable();
            $table->longText('photo')->after('address')->nullable();
            $table->longText('photo_id')->after('photo')->nullable();
            $table->longText('designation')->after('photo_id')->nullable();
            $table->longText('company_name')->after('designation')->nullable();
            $table->string('state')->after('company_name')->nullable();
            $table->string('city')->after('state')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
