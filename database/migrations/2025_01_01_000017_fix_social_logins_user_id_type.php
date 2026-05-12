<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Fixes user_id to string so it works with both integer (MySQL) and
// ObjectId-based (MongoDB-backed users) primary keys.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('oxalis_social_logins', function (Blueprint $table) {
            $table->string('user_id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('oxalis_social_logins', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });
    }
};
