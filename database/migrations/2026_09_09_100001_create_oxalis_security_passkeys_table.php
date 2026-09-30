<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function __construct()
    {
        $this->connection = config('oxalis.database.security_connection', config('database.default'));
    }

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('passkeys')) {
            return;
        }

        Schema::connection($this->connection)->create('passkeys', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 64)->index();
            $table->string('name');
            $table->string('credential_id', 2048)->unique();
            $table->json('credential');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('passkeys');
    }
};
