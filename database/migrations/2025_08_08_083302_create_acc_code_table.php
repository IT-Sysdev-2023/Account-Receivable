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
        Schema::create('acc_code', function (Blueprint $table) {
            $table->id();
            $table->integer('gl_account_id');
            $table->string('gl_account_navcode');
            $table->string('gl_account_name');
            $table->string('setup_by');
            $table->boolean('status');
            $table->string('business_unit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acc_code');
    }
};
