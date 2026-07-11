<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_payment_receiveds', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('party_name');
            $table->string('received_amount');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_payment_receiveds');
    }
};
