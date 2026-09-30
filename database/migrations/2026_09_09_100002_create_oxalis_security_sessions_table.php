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
        if (Schema::connection($this->connection)->hasTable('sessions')) {
            return;
        }

        Schema::connection($this->connection)->create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id', 64)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('sessions');
    }
};
