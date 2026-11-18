<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'status_pelanggan')) {
                $table->string('status_pelanggan', 30)->default('aktif')->after('paket_id')->index();
            }
            if (!Schema::hasColumn('customers', 'alasan_nonaktif')) {
                $table->string('alasan_nonaktif', 50)->nullable()->after('status_pelanggan');
            }
            if (!Schema::hasColumn('customers', 'nonaktif_at')) {
                $table->timestamp('nonaktif_at')->nullable()->after('alasan_nonaktif');
            }
        });

        // 1. Backfill untuk pelanggan yang saat ini sudah soft-delete (arsip) -> non_aktif
        DB::table('customers')->whereNotNull('deleted_at')->update([
            'status_pelanggan' => 'non_aktif',
            'alasan_nonaktif'  => 'tidak_berlangganan',
            'nonaktif_at'      => DB::raw('deleted_at'),
        ]);

        // 2. Backfill untuk pelanggan yang PPPoE-nya saat ini berstatus isolir
        try {
            $isolirUsernames = DB::table('pppoes')
                ->where('is_isolir', 1)
                ->pluck('username')
                ->filter()
                ->toArray();

            if (!empty($isolirUsernames)) {
                DB::table('customers')
                    ->whereNull('deleted_at')
                    ->whereIn('pppoe_username', $isolirUsernames)
                    ->update([
                        'status_pelanggan' => 'isolir',
                    ]);
            }
        } catch (\Throwable $e) {
            // Bypass jika tabel pppoes belum ada
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('customers', 'status_pelanggan')) {
                $columnsToDrop[] = 'status_pelanggan';
            }
            if (Schema::hasColumn('customers', 'alasan_nonaktif')) {
                $columnsToDrop[] = 'alasan_nonaktif';
            }
            if (Schema::hasColumn('customers', 'nonaktif_at')) {
                $columnsToDrop[] = 'nonaktif_at';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
