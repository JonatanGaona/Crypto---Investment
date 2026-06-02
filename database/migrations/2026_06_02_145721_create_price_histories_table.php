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
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cryptocurrency_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 16, 8); 
            $table->decimal('percent_change_24h', 8, 2);
            $table->decimal('volume_24h', 16, 2);
            $table->timestamp('recorded_at'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
