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
        Schema::create('dismantles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('id_admin')->constrained('users'); // Admin yang menugaskan
            $table->foreignId('id_teknisi_delegasi')->nullable()->constrained('users'); // Opsional (Tugas Khusus)
            $table->foreignId('id_teknisi_eksekutor')->nullable()->constrained('users'); // Yang mencabut

            $table->text('keterangan_admin')->nullable();
            $table->string('foto_pencabutan')->nullable(); // Bukti perangkat dicabut
            $table->enum('status', ['Pending', 'Selesai'])->default('Pending');

            $table->timestamp('delegated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dismantles');
    }
};
