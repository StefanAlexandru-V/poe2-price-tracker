<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('ninja_id')->index(); // e.g. "chaos", "divine", "mirror"
            $table->string('name');
            $table->string('slug');
            $table->string('category'); // Currency, Fragments, Essences, etc.
            $table->string('icon_url')->nullable();
            $table->string('details_id')->nullable();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ninja_id', 'league_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
