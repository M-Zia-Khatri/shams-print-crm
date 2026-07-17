<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_daily_laberi_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('laberi_date');
            $table->string('daily_shift');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'laberi_date']);
            $table->index('laberi_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_daily_laberi_entries');
    }
};
