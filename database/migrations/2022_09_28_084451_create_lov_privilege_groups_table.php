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
        Schema::create('lov_privilege_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->tinyInteger('is_default')->default(0)->comment("(1 => YES , 0 => NO) Default 0");
            $table->tinyInteger('is_active')->default(1)->comment("(1 => Active, 0 => Inactive) Default 0");
            
            // Laravel-managed timestamps + soft delete
            $table->timestamps();
            $table->softDeletes();
            
            // Audit fields for tracking user actions
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID of the user who created this privilege group');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID of the user who last updated this privilege group');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID of the user who soft deleted this privilege group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lov_privilege_groups');
    }
};
