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
        Schema::create('beginning_balance', function (Blueprint $table) {
            $table->id();
            $table->string('beginningbalance_no');
            $table->date('receipt_date');
            $table->date('transaction_date');
            $table->string('customer_code');
            $table->string('name');
            $table->string('particular');
            $table->decimal('balance_amount', 10, 2);
            $table->string('file')->nullable();
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
        Schema::dropIfExists('beginning_balance');
    }
};
