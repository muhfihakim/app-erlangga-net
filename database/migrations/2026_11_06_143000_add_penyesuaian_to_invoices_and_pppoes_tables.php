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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'penyesuaian')) {
                $table->decimal('penyesuaian', 10, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('invoices', 'catatan_penyesuaian')) {
                $table->string('catatan_penyesuaian', 255)->nullable()->after('penyesuaian');
            }
        });

        Schema::table('pppoes', function (Blueprint $table) {
            if (!Schema::hasColumn('pppoes', 'penyesuaian_tagihan')) {
                $table->decimal('penyesuaian_tagihan', 10, 2)->default(0)->after('diskon');
            }
            if (!Schema::hasColumn('pppoes', 'keterangan_penyesuaian')) {
                $table->string('keterangan_penyesuaian', 255)->nullable()->after('penyesuaian_tagihan');
            }
            if (!Schema::hasColumn('pppoes', 'penyesuaian_tipe')) {
                $table->string('penyesuaian_tipe', 20)->default('sekali')->after('keterangan_penyesuaian');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['penyesuaian', 'catatan_penyesuaian']);
        });

        Schema::table('pppoes', function (Blueprint $table) {
            $table->dropColumn(['penyesuaian_tagihan', 'keterangan_penyesuaian', 'penyesuaian_tipe']);
        });
    }
};
