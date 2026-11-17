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
        if (!Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('judul', 150);
                $table->string('gambar');
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->integer('urutan')->default(1);
                $table->text('link_url')->nullable();
                $table->string('tindakan_tipe', 50)->default('url'); // 'url', 'whatsapp', 'internal'
                $table->text('deskripsi')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('klik_count')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['is_active', 'urutan']);
                $table->index(['tanggal_mulai', 'tanggal_selesai']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
