<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oxalis_totp_secrets', function (Blueprint $table) {
            $table->json('recovery_codes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('oxalis_totp_secrets', function (Blueprint $table) {
            $table->dropColumn('recovery_codes');
        });
    }
};
