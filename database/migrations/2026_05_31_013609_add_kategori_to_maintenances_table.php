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
        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('kategori_kendala')->nullable()->after('partner');
            $table->string('sn_baru')->nullable()->after('kategori_kendala');
            $table->string('sn_lama')->nullable()->after('sn_baru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn(['kategori_kendala', 'sn_baru', 'sn_lama']);
        });
    }
};
