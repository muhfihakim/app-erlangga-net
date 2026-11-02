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
        // Reassign any leftover Web Developer users to Administrator just in case
        DB::table('users')->where('jabatan', 'Web Developer')->update(['jabatan' => 'Administrator']);

        DB::statement("ALTER TABLE users MODIFY COLUMN jabatan ENUM('Super Admin', 'Administrator', 'Sales', 'Teknisi') DEFAULT 'Teknisi'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN jabatan ENUM('Super Admin', 'Administrator', 'Sales', 'Teknisi', 'Web Developer') DEFAULT 'Teknisi'");
    }
};
