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
        Schema::dropIfExists('item_entries_colors');
        Schema::dropIfExists('item_entries_sizes');
        Schema::dropIfExists('item_entries_pieces');
        Schema::dropIfExists('item_entries');

        Schema::create('item_entries', function (Blueprint $table) {
            $table->id();
            $table->string('lart_number');
            $table->string('client_business_name');
            $table->string('description');
            $table->string('image_url');
            $table->integer('darjan');
            $table->integer('total_color');
            $table->decimal('total_rate', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->string('size_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_entries');
    }
};
