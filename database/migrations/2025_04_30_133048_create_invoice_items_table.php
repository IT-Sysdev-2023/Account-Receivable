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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no');
            $table->string('item_code');
            $table->string('item_name');
            $table->string('packing');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            $table->softDeletes()->index();

            // Add foreign key constraint
            $table->foreign('invoice_no')
                ->references('invoice_no')
                ->on('invoice');
            //->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('invoice_items', function (Blueprint $table) {
        //     $table->dropForeign(['invoice_no']);
        // });

        Schema::dropIfExists('invoice_items');
    }
};
