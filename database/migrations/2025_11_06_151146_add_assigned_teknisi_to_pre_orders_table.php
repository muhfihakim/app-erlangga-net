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
            $table->foreignId('id_teknisi_delegasi')->nullable()->constrained('users')->after('id_sales');
        });
    }

    public function down(): void
    {
        Schema::table('pre_orders', function (Blueprint $table) {
            $table->dropForeign(['id_teknisi_delegasi']);
            $table->dropColumn('id_teknisi_delegasi');
        });
    }
};
