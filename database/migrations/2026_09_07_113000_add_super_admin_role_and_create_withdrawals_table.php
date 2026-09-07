<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update jabatan ENUM in users table to include 'Super Admin'
        DB::statement("ALTER TABLE users MODIFY COLUMN jabatan ENUM('Super Admin', 'Administrator', 'Sales', 'Teknisi', 'Web Developer') DEFAULT 'Teknisi'");

        // 2. Create withdrawals table
        if (!Schema::hasTable('withdrawals')) {
            Schema::create('withdrawals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('reference_id')->unique();
                $table->decimal('amount', 15, 2);
                $table->string('bank_code', 50);
                $table->string('bank_name', 100)->nullable();
                $table->string('account_number', 50);
                $table->string('account_holder_name', 150);
                $table->string('description', 255)->nullable();
                $table->string('status', 30)->default('PENDING'); // PENDING, COMPLETED, FAILED
                $table->string('xendit_disbursement_id')->nullable()->index();
                $table->string('failure_code')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
        DB::statement("ALTER TABLE users MODIFY COLUMN jabatan ENUM('Administrator', 'Sales', 'Teknisi', 'Web Developer') DEFAULT 'Teknisi'");
    }
};
