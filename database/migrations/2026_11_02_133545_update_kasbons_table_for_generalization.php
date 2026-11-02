<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kasbons', function (Blueprint $table) {
            if (!Schema::hasColumn('kasbons', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('kasbons', 'status')) {
                $table->string('status')->default('approved')->after('tanggal'); // 'pending', 'approved', 'rejected'
            }
            if (!Schema::hasColumn('kasbons', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('kasbons', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('kasbons', 'catatan_admin')) {
                $table->text('catatan_admin')->nullable()->after('approved_at');
            }
        });

        // Backfill user_id from existing teknisi_id
        if (Schema::hasColumn('kasbons', 'teknisi_id') && Schema::hasColumn('kasbons', 'user_id')) {
            DB::statement("UPDATE kasbons SET user_id = teknisi_id WHERE user_id IS NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasbons', function (Blueprint $table) {
            if (Schema::hasColumn('kasbons', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('kasbons', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('kasbons', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('kasbons', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('kasbons', 'catatan_admin')) {
                $table->dropColumn('catatan_admin');
            }
        });
    }
};
