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
        Schema::create('products_price_types', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('price_type_id');
            
            // Foreign key constraints
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
                
            $table->foreign('price_type_id')
                ->references('id')
                ->on('price_types')
                ->onDelete('cascade');
            
            // Composite unique constraint to prevent duplicate relationships
            $table->unique(['product_id', 'price_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_price_types');
    }
};
