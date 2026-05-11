<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oxalis_invites', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('note')->nullable();
            $table->integer('max_uses')->default(1);
            $table->integer('uses')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oxalis_invites');
    }
};
