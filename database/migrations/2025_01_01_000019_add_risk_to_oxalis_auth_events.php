<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oxalis_auth_events', function (Blueprint $table) {
            $table->unsignedTinyInteger('risk_score')->default(0)->after('status');
            $table->string('device_fingerprint', 64)->nullable()->after('risk_score');
        });
    }

    public function down(): void
    {
        Schema::table('oxalis_auth_events', function (Blueprint $table) {
            $table->dropColumn(['risk_score', 'device_fingerprint']);
        });
    }
};
