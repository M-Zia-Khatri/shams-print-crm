<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->date('expense_date');
            $table->json('expense_list');
            $table->decimal('total_expense', 12, 2);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('expense_date');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};