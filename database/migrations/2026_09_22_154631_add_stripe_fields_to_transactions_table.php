<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->string('stripe_transfer_id')
                ->nullable()
                ->index();

            $table->string('stripe_payout_id')
                ->nullable()
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $table->dropColumn([
                'stripe_transfer_id',
                'stripe_payout_id',
            ]);
        });
    }
};