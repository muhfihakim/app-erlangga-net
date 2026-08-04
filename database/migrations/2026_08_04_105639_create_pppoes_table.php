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
        Schema::create('pppoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pre_order_id')->nullable()->constrained('pre_orders')->onDelete('set null');
            $table->string('username');
            $table->string('password');
            $table->string('profile_paket');
            $table->string('router')->nullable();
            $table->string('ip_address')->nullable();
            
            // Tab Pembayaran
            $table->string('tipe_pembayaran')->nullable(); // postpaid / prepaid
            $table->date('tanggal_aktif')->nullable();
            $table->string('periode_billing')->nullable(); // fixed / renewal
            $table->decimal('pajak', 10, 2)->default(0);
            $table->decimal('diskon', 10, 2)->default(0);
            $table->string('status_invoice')->default('belum_bayar'); // terbayar / belum_bayar
            $table->boolean('generate_invoice')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pppoes');
    }
};
