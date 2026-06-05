<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to alter the enum
        DB::statement("ALTER TABLE users MODIFY COLUMN jabatan ENUM('Administrator', 'Sales', 'Teknisi', 'Web Developer') DEFAULT 'Teknisi'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN jabatan ENUM('Administrator', 'Sales', 'Teknisi') DEFAULT 'Teknisi'");
    }
};
