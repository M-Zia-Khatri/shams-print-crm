<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_entries_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_entries_piece_id')->constrained('item_entries_pieces')->cascadeOnDelete();
            $table->string('type');
            $table->decimal('rate', 10, 2);
            $table->integer('type_color_count');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_entries_colors');
    }
};
