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
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->string('payment_no')->nullable()->unique();
            $table->date('receipt_date');
            $table->date('transaction_date');
            $table->string('customer_code');
            $table->string('name');
            $table->string('price_group');
            $table->enum('payment_mode', ['Account Receivables', 'Cash']);
            $table->string('chargeinvoice_type');
            $table->string('particular');
            $table->string('reference_no');
            $table->decimal('total_amount', 10, 2);
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
        Schema::dropIfExists('invoice');
    }
};
