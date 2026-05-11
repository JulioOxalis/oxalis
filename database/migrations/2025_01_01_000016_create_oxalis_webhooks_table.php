<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oxalis_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('secret', 64);
            $table->json('events');
            $table->string('note')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('last_fired_at')->nullable();
            $table->integer('failures')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oxalis_webhooks');
    }
};
