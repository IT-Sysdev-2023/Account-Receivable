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
        Schema::create('payment', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no')->unique();
            $table->date('receipt_date');
            $table->date('transaction_date');
            $table->string('customer_code');
            $table->string('name');
            $table->enum('payment_type', ['5A - Cash', '5B - Journal Voucher', '5C - Online Deposit', '5D - Check', '5E - Creditable(WHT)']);
            $table->string('type');
            $table->string('reference_no')->nullable();
            $table->string('ds_no')->nullable();
            $table->string('document_no');
            $table->date('document_date');
            $table->decimal('advpy_amount_paid', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->string('acc_code')->nullable();
            $table->string('cust_code')->nullable();
            $table->string('cash_in_bank')->nullable();
            $table->boolean('withBIR')->nullable();
            $table->string('witholdingtax')->nullable();
            $table->string('check_type')->nullable();
            $table->string('aging_basis')->nullable();
            $table->integer('aging_days')->nullable();
            $table->string('acc_name_address')->nullable();
            $table->string('referral_name')->nullable();
            $table->string('acc_number')->nullable();
            $table->date('due_date')->nullable();
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
        Schema::dropIfExists('payment');
    }
};
