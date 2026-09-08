<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add balance column to existing users table if it doesn't exist
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'balance')) {
                $table->decimal('balance', 15, 2)->default(0.00)->after('email');
            }
        });

        // 1. Assets Table (Investment Opportunities)
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category'); // e.g. Hospitality, Energy, Agriculture
            $table->text('description')->nullable();
            $table->decimal('total_valuation', 15, 2);
            $table->unsignedInteger('total_shares');
            $table->unsignedInteger('available_shares');
            $table->decimal('share_price', 12, 2);
            $table->enum('status', ['draft', 'active', 'funded', 'closed'])->default('active');
            $table->timestamps();
        });

        // 2. Holdings Table (Cap Table / User Ownership)
        Schema::create('holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('shares_owned')->default(0);
            $table->decimal('total_invested', 15, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['user_id', 'asset_id']);
        });

        // 3. Transactions Table (Immutable Audit Log)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['deposit', 'withdrawal', 'buy_equity', 'distribution']);
            $table->decimal('amount', 15, 2);
            $table->unsignedInteger('shares')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('holdings');
        Schema::dropIfExists('assets');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};