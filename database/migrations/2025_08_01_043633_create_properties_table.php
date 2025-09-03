<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id()->comment('Unique identifier for the property');
            $table->string('title')->comment('Title or primary identifier of the property listing');
            $table->text('description')->nullable()->comment('Detailed description of the property');

            // Location Details
            $table->string('street_address')->comment('Street address of the property');
            $table->string('unit', 50)->nullable()->comment('Unit number, apartment, or suite (if applicable)');
            $table->unsignedBigInteger('city_id')->comment('Foreign key to the cities table');
            $table->unsignedBigInteger('state_id')->comment('Foreign key to the states table');
            $table->unsignedBigInteger('country_id')->comment('Foreign key to the countries table');
            $table->string('zip_code', 20)->comment('Zip or postal code');
            $table->string('plus_code', 20)->nullable()->comment('Google Plus Code for the location');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Geographic latitude of the property');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Geographic longitude of the property');
            $table->string('timezone', 50)->nullable()->comment('Timezone of the property location (e.g., America/Los_Angeles)');

            // Property Characteristics
            $table->decimal('num_bedrooms', 3, 1)->nullable()->comment('Number of bedrooms (e.g., 3.0, 4.5)');
            $table->decimal('num_baths', 3, 1)->nullable()->comment('Number of full bathrooms (e.g., 2.0, 3.5)');
            $table->tinyInteger('num_half_baths')->nullable()->comment('Number of half bathrooms');
            $table->integer('square_footage')->nullable()->comment('Total living area in square feet');
            $table->decimal('lot_size_value', 15, 4)->nullable()->comment('Numerical value of the lot size');
            $table->enum('lot_size_unit', ['sqft', 'acres', 'hectares', 'sq_meters'])->nullable()->comment('Unit of the lot size (e.g., sqft, acres)');
            $table->enum('parking_type', ['Garage', 'Carport', 'Driveway', 'Street', 'None', 'Other'])->nullable()->comment('Type of parking available for the property');
            $table->smallInteger('year_built')->nullable()->comment('Year the property was originally built');

            // Financial Details
            $table->decimal('starting_price', 15, 2)->nullable()->comment('Initial asking price or starting bid price');
            $table->decimal('buy_now_price', 15, 2)->nullable()->comment('Price at which the property can be purchased immediately');
            $table->decimal('min_emd', 15, 2)->nullable()->comment('Minimum Earnest Money Deposit required');
            $table->decimal('original_purchased_emd', 15, 2)->nullable()->comment('Original EMD paid by the current seller (if applicable)');
            $table->decimal('purchase_price', 15, 2)->nullable()->comment('The price at which the property was acquired by the current seller');
            $table->decimal('arv', 15, 2)->nullable()->comment('After Repair Value of the property');
            $table->decimal('zestimate', 15, 2)->nullable()->comment('Zillow Zestimate value (if available)');

            // Deal Management
            $table->unsignedBigInteger('disposition_manager_id')->nullable()->comment('ID of the user (Disposition Manager) responsible for this property');
            $table->string('seller_name')->nullable()->comment('Name of the current property seller');
            $table->date('date_deal')->nullable()->comment('Date when the deal was initiated/acquired');
            $table->dateTime('accepting_offer_until')->nullable()->comment('Date and time until which offers are accepted');
            $table->enum('status', ['active', 'under_contract', 'sold', 'off_market', 'pending_review', 'archived'])->default('active')->comment('Current status of the property listing');

            // Audit Fields
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID of the user who created this property record');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID of the user who last updated this property record');
            $table->timestamp('deleted_at')->nullable()->comment('Timestamp when the property record was soft deleted');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID of the user who soft deleted this property record');

            // Indexes
            $table->index('title', 'idx_property_title');
            $table->index('street_address', 'idx_property_street_address');
            $table->index('city_id', 'idx_property_city_id');
            $table->index('state_id', 'idx_property_state_id');
            $table->index('country_id', 'idx_property_country_id');
            $table->index('zip_code', 'idx_property_zip_code');
            $table->index('parking_type', 'idx_property_parking_type');
            $table->index('year_built', 'idx_property_year_built');
            $table->index('starting_price', 'idx_property_starting_price');
            $table->index('buy_now_price', 'idx_property_buy_now_price');
            $table->index('disposition_manager_id', 'idx_property_disposition_manager_id');
            $table->index('status', 'idx_property_status');
            $table->index('date_deal', 'idx_property_date_deal');

            // Foreign Key Constraints
            $table->foreign('disposition_manager_id', 'fk_properties_disposition_manager_id')->references('id')->on('mst_users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('created_by', 'fk_properties_created_by')->references('id')->on('mst_users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('updated_by', 'fk_properties_updated_by')->references('id')->on('mst_users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('deleted_by', 'fk_properties_deleted_by')->references('id')->on('mst_users')->onDelete('set null')->onUpdate('cascade');
        });
        
        DB::statement("ALTER TABLE `properties` COMMENT = 'Table to store property details'");
    }

    public function down()
    {
        Schema::dropIfExists('properties');
    }
};