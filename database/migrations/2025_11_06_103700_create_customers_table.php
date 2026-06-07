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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // --- Data Pelanggan yang Diambil dari PO (Foreign Key) ---
            $table->foreignId('pre_order_id')->unique()->constrained('pre_orders')->onDelete('cascade')->nullable();
            $table->foreignId('id_sales')->constrained('users')->nullable(); // Sales yang membuat PO
            $table->foreignId('id_teknisi')->constrained('users')->nullable(); // Teknisi yang mengaktifkan
            $table->string('nama_lengkap', 255)->nullable();
            $table->string('alamat_lengkap', 500)->nullable();
            $table->string('no_whatsapp', 20)->nullable();
            $table->string('paket_internet', 255)->nullable();

            // --- Data Aktivasi Teknis ---
            $table->string('id_pelanggan')->unique()->nullable();
            $table->string('pppoe_username')->unique()->nullable();
            $table->string('pppoe_password')->nullable();
            $table->enum('jenis_modem', ['EPON', 'GPON'])->nullable();
            $table->string('sn_perangkat')->nullable();

            // --- Data Administrasi ---
            $table->enum('pembayaran', ['Cash', 'Aplikasi']);
            $table->string('partner', 255)->nullable(); // Misal: Nama partner/partner kedua
            $table->json('partner_ids')->nullable();
            $table->text('keterangan')->nullable();

            // --- Data Foto ---
            $table->string('foto_petugas')->nullable();
            $table->string('foto_terpasang')->nullable();
            $table->string('foto_speedtest')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
