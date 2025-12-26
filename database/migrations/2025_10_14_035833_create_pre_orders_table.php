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
        Schema::create('pre_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sales')->constrained('users')->onDelete('cascade');
            $table->string('nama_sales');
            $table->string('paket_internet');
            $table->string('nama_lengkap');
            $table->string('no_identitas');
            $table->string('no_whatsapp');
            $table->string('alamat_email');
            $table->text('alamat_lengkap');
            $table->string('titik_koordinat');
            $table->string('foto_ktp')->nullable();
            $table->string('foto_rumah')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_orders');
    }
};
