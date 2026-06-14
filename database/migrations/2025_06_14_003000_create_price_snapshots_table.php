<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('divine_value', 16, 8); // price in divine orbs
            $table->decimal('volume', 16, 2)->default(0);
            $table->decimal('change_7d', 8, 2)->nullable(); // sparkline total change
            $table->timestamp('snapshot_at');

            $table->index(['item_id', 'snapshot_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_snapshots');
    }
};
