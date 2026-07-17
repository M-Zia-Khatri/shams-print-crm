<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_paid_laberis', function (Blueprint $table) {
            $table->index(['employee_id', 'paid_date', 'payment_type'], 'employee_paid_laberis_employee_date_type_index');
        });
    }

    public function down(): void
    {
        Schema::table('employee_paid_laberis', function (Blueprint $table) {
            $table->dropIndex('employee_paid_laberis_employee_date_type_index');
        });
    }
};
