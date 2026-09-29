<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pppoes', function (Blueprint $table) {
            $table->boolean('is_isolir')->default(false)->after('status_invoice')->index();
            $table->string('isolir_reason')->nullable()->after('is_isolir');
            $table->timestamp('isolir_at')->nullable()->after('isolir_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pppoes', function (Blueprint $table) {
            $table->dropIndex(['is_isolir']);
            $table->dropColumn(['is_isolir', 'isolir_reason', 'isolir_at']);
        });
    }
};
