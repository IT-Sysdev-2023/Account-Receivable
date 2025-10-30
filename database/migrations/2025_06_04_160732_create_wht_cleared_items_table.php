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
        Schema::create('wht_cleared_items', function (Blueprint $table) {
            $table->id();
            $table->string('wht_clearing_no');
            $table->string('payment_no');
            $table->string('wht_no');
            $table->string('document_no');
            $table->date('receipt_date');
            $table->decimal('amount', 10, 2);
            $table->string('status');
            $table->string('remarks')->nullable();
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('wht_clearing_no')
                ->references('wht_clearing_no')
                ->on('wht_cleared');
            //->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wht_cleared_items');
    }
};
