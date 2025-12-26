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
            $table->foreignId('pre_order_id')->unique()->constrained('pre_orders')->onDelete('cascade');
            $table->foreignId('id_sales')->constrained('users'); // Sales yang membuat PO
            $table->foreignId('id_teknisi')->constrained('users'); // Teknisi yang mengaktifkan
            $table->string('nama_lengkap', 255);
            $table->string('alamat_lengkap', 500);
            $table->string('no_whatsapp', 20);
            $table->string('paket_internet', 255);

            // --- Data Aktivasi Teknis ---
            $table->string('id_pelanggan')->unique();
            $table->string('pppoe_username')->unique();
            $table->string('pppoe_password');
            $table->enum('jenis_modem', ['EPON', 'GPON']);
            $table->string('sn_perangkat');

            // --- Data Administrasi ---
            $table->enum('pembayaran', ['Cash', 'Transfer', 'Aplikasi']);
            $table->string('partner', 255)->nullable(); // Misal: Nama partner/partner kedua
            $table->text('keterangan')->nullable();

            // --- Data Foto ---
            $table->string('foto_petugas')->nullable();
            $table->string('foto_terpasang')->nullable();
            $table->string('foto_speedtest')->nullable();

            $table->timestamps();
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
