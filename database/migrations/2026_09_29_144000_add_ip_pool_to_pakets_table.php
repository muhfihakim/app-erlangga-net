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
        Schema::table('pakets', function (Blueprint $table) {
            $table->string('local_address')->nullable()->after('rate_limit');
            $table->string('ip_pool')->nullable()->after('local_address');
            $table->string('pool_range')->nullable()->after('ip_pool');
        });

        // Set default value untuk paket yang sudah ada
        DB::table('pakets')->where('nama_paket', 'like', '%bronze%')->orWhere('nama_paket', 'like', '%112%')->update([
            'local_address' => '182.10.0.1',
            'ip_pool' => 'pool-10m',
            'pool_range' => '182.10.0.2-182.10.255.254',
        ]);

        DB::table('pakets')->where('nama_paket', 'like', '%silver%')->orWhere('nama_paket', 'like', '%179%')->update([
            'local_address' => '182.20.0.1',
            'ip_pool' => 'pool-20m',
            'pool_range' => '182.20.0.2-182.20.255.254',
        ]);

        DB::table('pakets')->where('nama_paket', 'like', '%gold%')->orWhere('nama_paket', 'like', '%239%')->update([
            'local_address' => '182.30.0.1',
            'ip_pool' => 'pool-30m',
            'pool_range' => '182.30.0.2-182.30.255.254',
        ]);

        DB::table('pakets')->where('nama_paket', 'like', '%platinum%')->orWhere('nama_paket', 'like', '%279%')->update([
            'local_address' => '182.50.0.1',
            'ip_pool' => 'pool-50m',
            'pool_range' => '182.50.0.2-182.50.255.254',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
            $table->dropColumn(['local_address', 'ip_pool', 'pool_range']);
        });
    }
};
