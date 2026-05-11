<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oxalis_admin_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('password_hash');
            $table->string('totp_secret')->nullable();
            $table->timestamp('totp_confirmed_at')->nullable();
            $table->string('session_version', 32)->default('initial');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oxalis_admin_credentials');
    }
};
