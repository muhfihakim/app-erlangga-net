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
        Schema::create('supporting_activities', function (Blueprint $table) {
            $table->id();
            $table->enum('activity_type', ['PO', 'Pemeliharaan', 'Buat ODP', 'Aktivasi Jalur']);
            $table->text('description')->nullable();
            $table->foreignId('main_technician_id')->constrained('users');
            $table->json('partner_ids')->nullable();
            $table->string('photo')->nullable();
            $table->date('activity_date');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supporting_activities');
    }
};
