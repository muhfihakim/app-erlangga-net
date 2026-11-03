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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30)->index();
            $table->string('customer_name')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('pppoe_id')->nullable()->index();
            $table->unsignedBigInteger('pre_order_id')->nullable()->index();
            $table->unsignedBigInteger('sender_id')->nullable()->index(); // ID Admin pengirim
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound')->index();
            $table->enum('type', ['text', 'invoice', 'template', 'system', 'otp'])->default('text');
            $table->text('message');
            $table->string('media_url')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed', 'simulated'])->default('sent');
            $table->json('metadata')->nullable(); // Untuk invoice_id, detail respon provider, dsb.
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
