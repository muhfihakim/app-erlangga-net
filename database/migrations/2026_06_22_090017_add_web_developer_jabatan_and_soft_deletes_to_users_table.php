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
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
        DB::statement("ALTER TABLE users MODIFY COLUMN jabatan ENUM('Administrator', 'Sales', 'Teknisi', 'Web Developer') DEFAULT 'Teknisi'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        DB::statement("ALTER TABLE users MODIFY COLUMN jabatan ENUM('Administrator', 'Sales', 'Teknisi') DEFAULT 'Teknisi'");
    }
};
