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
        Schema::create('adj_reason_setup', function (Blueprint $table) {
            $table->id();
            $table->string('reason_name');
            $table->string('acc_code');
            $table->enum('type', [
                'Sales Invoice',
                'Other Income',
                'Payment',
            ]);
            $table->enum('status', ['Active', 'Inactive']);
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adj_reason_setup');
    }
};
