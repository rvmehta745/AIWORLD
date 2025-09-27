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
        Schema::create('categories_products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_type_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('product_id');

            // Add foreign key constraints
            $table->foreign('product_type_id')
                ->references('id')->on('product_types')
                ->onDelete('cascade');
                
            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->onDelete('cascade');
                
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('cascade');

            // Add composite primary key
            $table->primary(['category_id', 'product_id']);
            
            // Add index for better performance
            $table->index(['product_type_id', 'category_id']);
            $table->index(['product_type_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories_products');
    }
};
