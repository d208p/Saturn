<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kyc_status')->default('unverified'); // unverified, pending, verified
            $table->boolean('identity_verified')->default(false);
            $table->boolean('address_verified')->default(false);
            $table->boolean('selfie_verified')->default(false);
            $table->boolean('accredited_investor')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_status',
                'identity_verified',
                'address_verified',
                'selfie_verified',
                'accredited_investor',
            ]);
        });
    }
};