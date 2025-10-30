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
        Schema::create('check_cleared', function (Blueprint $table) {
            $table->id();
            $table->string('clearing_no')->unique();
            $table->date('transaction_date');
            $table->date('clearing_date');
            $table->string('check_type');
            $table->string('customer_code');
            $table->string('customer_name');
            $table->string('created_by');
            $table->timestamps();
            $table->softDeletes()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_cleared');
    }
};
