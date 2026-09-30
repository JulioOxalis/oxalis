<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('oxalis_totp_secrets', function (Blueprint $table) {
            // Stores the Google2FA time-step index of the last accepted code.
            // Used by verifyKeyNewer() to reject replayed codes within the same 30-second window.
            $table->unsignedInteger('last_totp_ts')->nullable()->after('recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('oxalis_totp_secrets', function (Blueprint $table) {
            $table->dropColumn('last_totp_ts');
        });
    }
};
