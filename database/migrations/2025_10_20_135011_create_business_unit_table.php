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
        Schema::create('business_unit', function (Blueprint $table) {
            $table->id();
            $table->integer('bu_id')->unique();
            $table->string('dashboard_path');
            $table->string('name');
            $table->string('database');
            $table->string('host');
            $table->string('port');
            $table->string('username');
            $table->string('password');
            $table->string('business_unit');
            $table->string('business_unit_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_unit');
    }
};
