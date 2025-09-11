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
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // varchar(100), not null
            $table->string('slug')->unique(); // varchar(191), not null
            $table->string('tag_line')->nullable(); // varchar(255), not null
            $table->text('configuration')->nullable(); // text, nullable
            $table->integer('sort_order')->nullable(); // int, nullable
            $table->enum('status', ['Active', 'InActive'])->default('InActive'); // enum, default InActive
            
            // Laravel-managed timestamps + soft delete
            $table->softDeletes();
            $table->timestamps();
            
            // Audit fields for tracking user actions
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID of the user who created this product type');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID of the user who last updated this product type');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID of the user who soft deleted this product type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_types');
    }
};
