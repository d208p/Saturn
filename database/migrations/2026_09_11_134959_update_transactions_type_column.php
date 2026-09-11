<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Extinderea lungimii pentru coloana 'type'
            $table->string('type', 50)->change();

            // Schimbarea coloanei 'status' la string simplu pentru a permite 'listed'
            $table->string('status')->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('type', 10)->change();
            $table->enum('status', ['completed', 'pending', 'cancelled'])->change();
        });
    }
};