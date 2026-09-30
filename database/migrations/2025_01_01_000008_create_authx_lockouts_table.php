<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oxalis_lockouts', function (Blueprint $table) {
            $table->id();
            $table->string('key', 128)->unique(); // "{email}|{ip}"
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('locked_until')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('oxalis_lockouts'); }
};
