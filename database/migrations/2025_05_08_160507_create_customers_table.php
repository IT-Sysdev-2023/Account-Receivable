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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->integer('cus_id');
            $table->string('cus_code');
            $table->string('cus_name');
            $table->string('cus_type');
            $table->string('cus_price_group')->nullable();
            $table->string('cus_address');
            $table->string('cus_tin');
            $table->string('cus_currency');
            $table->string('cus_bu');
            $table->string('nav_code')->nullable();
            $table->decimal('credit_limit', 10, 2);
            $table->string('payment_terms')->nullable();
            $table->boolean('non_trade');
            $table->boolean('applies_shrinkage');
            $table->boolean('editable_wht');
            $table->boolean('journal_voucher');
            $table->string('gen_posting')->nullable();
            $table->string('cus_posting')->nullable();
            $table->string('vat_posting')->nullable();
            $table->string('cus_status');
            $table->string('setup_by');
            $table->decimal('advanced_payment_balance', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
