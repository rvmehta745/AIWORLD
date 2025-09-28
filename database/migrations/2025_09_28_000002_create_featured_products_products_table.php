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
        Schema::create('featured_products_products', function (Blueprint $table) {
            $table->unsignedBigInteger('featured_product_id');
            $table->unsignedBigInteger('product_id');

            // Add foreign key constraints
            $table->foreign('featured_product_id')
                ->references('id')->on('featured_products')
                ->onDelete('cascade');
                
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('cascade');

            // Add composite primary key
            $table->primary(['featured_product_id', 'product_id']);
            
            // Add index for better performance
            $table->index(['featured_product_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_products_products');
    }
}; 