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
        Schema::create('platform_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Buyer who paid the fee
            $table->foreignId('asset_id')->constrained()->onDelete('cascade'); // Asset traded
            $table->string('source')->default('secondary_market'); // Origin: secondary_market, primary_market, withdrawal
            $table->decimal('amount', 15, 2); // Fee amount in EUR
            $table->decimal('fee_percentage', 5, 2); // e.g., 3.30
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_fees');
    }
};