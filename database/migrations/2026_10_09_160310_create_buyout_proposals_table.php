<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyout_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('buyer_name');
            $table->decimal('offered_amount', 15, 2); // Total offered buyout price
            $table->timestamp('voting_ends_at');
            $table->enum('status', ['voting', 'approved_pending_payout', 'completed', 'rejected'])->default('voting');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyout_proposals');
    }
};