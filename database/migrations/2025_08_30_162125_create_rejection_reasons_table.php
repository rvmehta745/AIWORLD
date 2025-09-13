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
        Schema::create('rejection_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'InActive'])->default('Active');
            
            // Laravel-managed timestamps + soft delete
            $table->softDeletes();
            $table->timestamps();
            
            // Audit fields for tracking user actions
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID of the user who created this rejection reason');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID of the user who last updated this rejection reason');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID of the user who soft deleted this rejection reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rejection_reasons');
    }
};
