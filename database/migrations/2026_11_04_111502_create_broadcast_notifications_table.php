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
        if (!Schema::hasTable('broadcast_notifications')) {
            Schema::create('broadcast_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('body');
                $table->string('type', 50)->default('announcement'); // maintenance, billing, promo, announcement
                $table->string('target_type', 50)->default('all'); // all, package, customer
                $table->unsignedBigInteger('target_id')->nullable();
                $table->integer('total_targeted')->default(0);
                $table->integer('total_sent')->default(0);
                $table->integer('total_failed')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['type', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcast_notifications');
    }
};
