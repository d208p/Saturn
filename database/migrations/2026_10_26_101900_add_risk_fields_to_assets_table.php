<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Development exposure percentage or score set by admin (e.g., 0.00 to 100.00)
            $table->decimal('development_exposure', 5, 2)->default(0.00)->after('description');
            
            // Market liquidity / real-world sellability score set by admin (e.g., 1 to 10 or low/moderate/high)
            $table->string('market_liquidity')->default('moderate')->after('development_exposure');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['development_exposure', 'market_liquidity']);
        });
    }
};