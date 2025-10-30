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
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no');
            $table->string('check_no')->nullable();
            $table->string('document_no');
            $table->date('document_date');
            $table->date('payment_receipt_date')->nullable();
            $table->date('payment_date');
            $table->string('payment_type');
            $table->string('type');
            $table->string('customer_code');
            $table->string('customer_name');
            $table->string('check_type');
            $table->decimal('advpy_amount_paid', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->date('due_date')->nullable();
            $table->date('clearing_date')->nullable();
            $table->string('status');
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('payment_details');
    }
};
