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
        Schema::table('pre_orders', function (Blueprint $table) {
            // Kolom untuk mencatat kapan PO didelegasikan (untuk perhitungan deadline)
            $table->timestamp('delegated_at')->nullable()->after('id_teknisi_delegasi');
        });
    }

    public function down(): void
    {
        Schema::table('pre_orders', function (Blueprint $table) {
            $table->dropColumn('delegated_at');
        });
    }
};
