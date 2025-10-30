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
        Schema::create('adjustment', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_no')->unique();
            $table->date('receipt_date');
            $table->date('transaction_date');
            $table->string('customer_code');
            $table->string('name');
            $table->enum('type', ['Positive', 'Negative']);
            $table->enum('apply_to', ['Sales Invoice', 'Other Income']);
            $table->string('invoice_no');
            $table->decimal('balance', 10, 2);
            $table->string('adjustment_reason');
            $table->string('particulars');
            $table->decimal('amount', 10, 2);
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
        Schema::dropIfExists('adjustment');
    }
};
