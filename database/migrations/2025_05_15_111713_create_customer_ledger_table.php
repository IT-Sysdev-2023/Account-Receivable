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
        Schema::create('customer_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->date('date');
            $table->string('type');
            $table->string('customer_code');
            $table->string('customer_name');
            $table->string('currency');
            $table->decimal('amount',10,2);
            $table->decimal('adjusted_amount',10,2);
            $table->decimal('amount_paid',10,2);
            $table->decimal('running_balance',10,2);
            $table->string('trade_type')->nullable();
            $table->decimal('shrinkage',10,2)->nullable();
            $table->decimal('overage',10,2)->nullable();
            $table->decimal('return',10,2)->nullable();
            $table->string('si_payment_type')->nullable();
            $table->timestamps();
            $table->softDeletes()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_ledger');
    }
};
