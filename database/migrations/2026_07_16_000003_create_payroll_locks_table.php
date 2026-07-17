<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_locks', function (Blueprint $table) {
            $table->id();
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->foreignId('locked_by')->constrained('users');
            $table->timestamp('locked_at');
            $table->timestamps();

            $table->unique(['week_start_date', 'week_end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_locks');
    }
};
