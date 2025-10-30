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
        Schema::create('cancelled_payments', function (Blueprint $table) {
            $table->id();
            $table->string('cancellation_no')->unique();
            $table->string('payment_no')->nullable();
            $table->string('document_no')->nullable();
            $table->string('type');
            $table->string('customer_code');
            $table->string('customer_name');
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancelled_payments');
    }
};
