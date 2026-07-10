<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_entries_pieces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_entries_id')->constrained('item_entries')->cascadeOnDelete();
            $table->string('name');
            $table->integer('total_pieces');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_entries_pieces');
    }
};
