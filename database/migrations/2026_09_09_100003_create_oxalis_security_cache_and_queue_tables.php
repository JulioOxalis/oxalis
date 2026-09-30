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
        if (! Schema::connection($this->connection)->hasTable('cache')) {
            Schema::connection($this->connection)->create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        if (! Schema::connection($this->connection)->hasTable('cache_locks')) {
            Schema::connection($this->connection)->create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
        }

        if (! Schema::connection($this->connection)->hasTable('jobs')) {
            Schema::connection($this->connection)->create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (! Schema::connection($this->connection)->hasTable('job_batches')) {
            Schema::connection($this->connection)->create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        if (! Schema::connection($this->connection)->hasTable('failed_jobs')) {
            Schema::connection($this->connection)->create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('failed_jobs');
        Schema::connection($this->connection)->dropIfExists('job_batches');
        Schema::connection($this->connection)->dropIfExists('jobs');
        Schema::connection($this->connection)->dropIfExists('cache_locks');
        Schema::connection($this->connection)->dropIfExists('cache');
    }
};
