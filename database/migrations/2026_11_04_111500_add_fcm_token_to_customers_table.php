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
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'fcm_token')) {
                $table->text('fcm_token')->nullable()->after('sn_perangkat');
            }
            if (!Schema::hasColumn('customers', 'fcm_updated_at')) {
                $table->timestamp('fcm_updated_at')->nullable()->after('fcm_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'fcm_token')) {
                $table->dropColumn(['fcm_token', 'fcm_updated_at']);
            }
        });
    }
};
