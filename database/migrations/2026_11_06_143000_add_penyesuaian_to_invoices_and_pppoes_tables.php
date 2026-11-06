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
            $table->decimal('penyesuaian', 10, 2)->default(0)->after('amount');
            $table->string('catatan_penyesuaian', 255)->nullable()->after('penyesuaian');
        });

        Schema::table('pppoes', function (Blueprint $table) {
            $table->decimal('penyesuaian_tagihan', 10, 2)->default(0)->after('diskon');
            $table->string('keterangan_penyesuaian', 255)->nullable()->after('penyesuaian_tagihan');
            $table->string('penyesuaian_tipe', 20)->default('sekali')->after('keterangan_penyesuaian');
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
