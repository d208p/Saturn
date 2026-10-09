<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyout_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyout_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('vote', ['yes', 'no']);
            $table->unsignedInteger('weight'); // Number of shares held at vote time
            $table->timestamps();

            $table->unique(['buyout_proposal_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyout_votes');
    }
};