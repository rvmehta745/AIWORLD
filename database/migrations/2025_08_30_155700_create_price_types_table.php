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
        Schema::create('price_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_type_id');
            $table->string('name')->unique(); // name column, unique
            $table->enum('status', ['Active', 'InActive'])->default('InActive'); // enum status
            
            // Laravel-managed timestamps + soft delete
            $table->softDeletes();
            $table->timestamps();
            
            // Audit fields for tracking user actions
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID of the user who created this price type');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID of the user who last updated this price type');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID of the user who soft deleted this price type');
            $table->foreign('product_type_id', 'fk_price_types_product_type_id')
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
        Schema::dropIfExists('price_types');
    }
};
