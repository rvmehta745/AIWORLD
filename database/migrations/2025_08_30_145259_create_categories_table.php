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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_type_id'); // FK to product_types
            $table->unsignedBigInteger('parent_id')->nullable(); // Self relation
            $table->string('name');
            $table->string('slug')->unique(); // unique slug
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->integer('tools_count')->default(0);
            $table->enum('status', ['Active', 'InActive'])->default('InActive');
            $table->integer('sort_order')->nullable();
            
            // Laravel-managed timestamps + soft delete
            $table->softDeletes();
            $table->timestamps();
            
            // Audit fields for tracking user actions
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID of the user who created this category');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID of the user who last updated this category');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID of the user who soft deleted this category');
            $table->foreign('product_type_id', 'fk_categories_product_type_id')
                ->references('id')
                ->on('product_types')
                ->onDelete('cascade');

            $table->foreign('parent_id', 'fk_categories_parent_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
