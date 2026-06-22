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
            $table->foreignId('id_sales')->constrained('users')->onDelete('cascade')->nullable();
            $table->string('nama_sales')->nullable();
            $table->string('paket_internet')->nullable();
            $table->string('nama_lengkap')->nullable();
            $table->string('no_identitas')->nullable();
            $table->string('no_whatsapp')->nullable();
            $table->string('alamat_email')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('titik_koordinat')->nullable();
            $table->string('foto_ktp')->nullable()->nullable();
            $table->string('foto_rumah')->nullable()->nullable();
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
