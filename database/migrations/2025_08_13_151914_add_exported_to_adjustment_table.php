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
        Schema::table('adjustment', function (Blueprint $table) {
            $table->boolean('exported')->default(false)->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adjustment', function (Blueprint $table) {
            if (Schema::hasColumn('adjustment', 'exported')) {
                $table->dropColumn('exported');
            }
        });
    }
};
