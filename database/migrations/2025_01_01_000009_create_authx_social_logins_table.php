<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oxalis_social_logins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('provider', 30)->index();      // google, github
            $table->string('provider_id')->index();
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();
            $table->unique(['provider', 'provider_id']);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('oxalis_social_logins'); }
};
