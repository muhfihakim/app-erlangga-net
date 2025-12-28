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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('id_admin')->constrained('users');
            $table->foreignId('id_teknisi_delegasi')->nullable()->constrained('users'); // Opsional

            // Data Penugasan (Admin)
            $table->text('keterangan_kendala');
            $table->string('foto_kendala_awal')->nullable();
            $table->timestamp('delegated_at')->nullable();

            // Data Penyelesaian (Teknisi)
            $table->foreignId('id_teknisi_eksekutor')->nullable()->constrained('users'); // Siapa yang klik selesai
            $table->json('partner_ids')->nullable(); // Multi-id partner
            $table->text('partner')->nullable(); // Nama partner string
            $table->text('solusi_kendala')->nullable();
            $table->string('foto_sebelum_perbaikan')->nullable();
            $table->string('foto_setelah_perbaikan')->nullable();
            $table->string('foto_speedtest')->nullable();
            $table->string('foto_selfie_teknisi')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->enum('status', ['Pending', 'Selesai'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
