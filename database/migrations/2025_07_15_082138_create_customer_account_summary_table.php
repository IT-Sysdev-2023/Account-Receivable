<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW customer_account_summary AS
            SELECT
                customer_code,
                customer_name,
                SUM(amount) AS total_amount,
                SUM(running_balance) AS current_balance,
                SUM(amount_paid) AS total_amount_paid,
                SUM(adjusted_amount) AS total_adjusted,
                SUM(shrinkage) AS total_shrinkage,
                SUM(overage) AS total_overage,
                SUM(`return`) AS total_return,
                MAX(updated_at) AS last_activity
            FROM customer_ledger
            WHERE (si_payment_type IS NULL OR si_payment_type != 'Cash')
            AND deleted_at IS NULL
            GROUP BY customer_code, customer_name
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS customer_account_summary");
    }
};
