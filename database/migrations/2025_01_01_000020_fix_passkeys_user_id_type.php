<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Align passkeys.user_id with MongoDB / UUID primary keys (same as oxalis_sessions).
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('oxalis_passkeys')) {
            return;
        }

        Schema::table('oxalis_passkeys', function (Blueprint $table) {
            $table->string('user_id')->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('oxalis_passkeys')) {
            return;
        }

        Schema::table('oxalis_passkeys', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });
    }
};
