<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('mst_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug')->unique();
            $table->text('privileges')->nullable();
            $table->tinyInteger('is_editable')->default('1')->comment('(0 = No and 1 = Yes)');
            $table->tinyInteger('is_active')->nullable()->default(1)->comment('(0 => Inactive, 1 => Active) default 1');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_roles');
    }
};
