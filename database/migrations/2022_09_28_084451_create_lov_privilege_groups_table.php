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
    public function up()
    {
        Schema::create('lov_privilege_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->tinyInteger('is_default')->default(0)->comment("(1 => YES , 0 => NO) Default 0");
            $table->tinyInteger('is_active')->default(1)->comment("(1 => Active, 0 => Inactive) Default 0");
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lov_privilege_groups');
    }
};
