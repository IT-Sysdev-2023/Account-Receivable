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
        Schema::create('check_cleared_items', function (Blueprint $table) {
            $table->id();
            $table->string('clearing_no');
            $table->string('payment_no');
            $table->string('check_no');
            $table->string('document_no');
            $table->date('due_date');
            $table->decimal('amount', 10, 2);
            $table->string('status');
            $table->string('remarks')->nullable();
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('clearing_no')
                ->references('clearing_no')
                ->on('check_cleared');
            //->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_cleared_items');
    }
};
