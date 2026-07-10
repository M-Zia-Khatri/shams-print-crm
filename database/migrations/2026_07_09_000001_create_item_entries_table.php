<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_entries', function (Blueprint $table) {
            $table->id();
            $table->string('lart_number');
            $table->string('client_business_name');
            $table->string('description');
            $table->string('image_url');
            $table->integer('darjan');
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_entries');
    }
};
