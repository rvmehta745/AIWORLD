<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forget_password', function (Blueprint $table) {
            $table->id()->comment('Unique identifier for the OTP-based password reset request');
            $table->unsignedBigInteger('user_id')->comment('ID of the user requesting the password reset');
            $table->string('otp_code', 10)->comment('The generated One-Time Password (e.g., 6-8 digits/characters)');
            $table->timestamp('expires_at')->comment('Timestamp when the OTP expires (typically very short, e.g., 5-10 minutes)');
            $table->timestamp('used_at')->nullable()->comment('Timestamp when the OTP was successfully used to reset password');
            $table->tinyInteger('attempts')->default(0)->comment('Number of incorrect OTP attempts for this request');

            // Laravel-managed timestamps & soft delete
            $table->timestamps();   // created_at & updated_at
            $table->softDeletes();  // deleted_at

            // Extra audit fields
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID of the user who initiated this OTP request (if applicable)');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID of the user who last updated this record (system or admin)');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID of the user who soft deleted this record (if applicable)');

            // Indexes
            $table->index('user_id', 'idx_user_id');
            $table->index('otp_code', 'idx_otp_code');
            $table->index('expires_at', 'idx_expires_at');
            $table->index('created_by', 'idx_created_by');
            $table->index('updated_by', 'idx_updated_by');
            $table->index('deleted_by', 'idx_deleted_by');

            // Foreign key constraint
            $table->foreign('user_id', 'fk_forget_password_user_id_otp')
                ->references('id')->on('mst_users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        DB::statement("ALTER TABLE `forget_password` COMMENT = 'Table to store OTPs for forget password requests with full audit trail'");
    }

    public function down(): void
    {
        Schema::dropIfExists('forget_password');
    }
};
