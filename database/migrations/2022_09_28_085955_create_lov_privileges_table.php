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
        Schema::create('lov_privileges', function (Blueprint $table) {
            $table->id();
            $table->integer('sequence')->unsigned();
            $table->bigInteger('group_id');
            $table->bigInteger('parent_id');
            $table->string('path', 400);
            $table->string('name', 50);
            $table->string('permission_key', 400);
            $table->tinyInteger('is_active')->default(1)->comment("(1 => Active, 0 => Inactive) default 1");
            
            // Laravel-managed timestamps + soft delete
            $table->timestamps();
            $table->softDeletes();
            
            // Audit fields for tracking user actions
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID of the user who created this privilege');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID of the user who last updated this privilege');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID of the user who soft deleted this privilege');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lov_privileges');
    }
};
