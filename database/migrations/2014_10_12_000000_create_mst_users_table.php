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
        Schema::create('mst_users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50)->comment('User\'s first name');
            $table->string('last_name', 50)->comment('User\'s last name');
            $table->string('email', 100)->unique()->comment('User\'s email address, must be unique');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone_number', 20)->nullable()->comment('User\'s phone number (optional)');
            $table->string('country_code', 20)->nullable()->comment('Country code of phone number (optional)');
            $table->text('address')->nullable()->comment('User\'s address(optional)');
            $table->string('password', 255)->comment('Hashed password for security');
            $table->enum('role', ['Admin', 'Disposition Manager', 'Buyer'])->default('Buyer')->comment('User role within the system');
            $table->boolean('is_active')->default(true)->comment('Indicates if the user account is active');
            $table->integer('created_by')->nullable()->comment('ID of the user who created this record (if applicable)');
            $table->integer('updated_by')->nullable()->comment('ID of the user who last updated this record (if applicable)');
            $table->integer('deleted_by')->nullable()->comment('ID of the user who soft deleted this record (if applicable)');
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('email', 'idx_email');
            $table->index('first_name', 'idx_first_name');
            $table->index('last_name', 'idx_last_name');
            $table->index('role', 'idx_role');
            $table->index('created_by', 'idx_created_by');
            $table->index('updated_by', 'idx_updated_by');
            $table->index('deleted_by', 'idx_deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_users');
    }
};
