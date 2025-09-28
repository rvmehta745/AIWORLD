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
        Schema::create('featured_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_type_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('featured_url')->nullable();
            $table->integer('sort_order')->default(0);
            
            // Laravel-managed timestamps + soft delete
            $table->softDeletes();
            $table->timestamps();
            
            // Audit fields for tracking user actions
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID of the user who created this featured product');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID of the user who last updated this featured product');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID of the user who soft deleted this featured product');
            
            // Foreign key constraint
            $table->foreign('product_type_id', 'fk_featured_products_product_type_id')
                ->references('id')
                ->on('product_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_products');
    }
}; 