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
        Schema::create('lov_privileges', function (Blueprint $table) {
            $table->id();
            $table->integer('sequence')->unsigned();
            $table->bigInteger('group_id');
            $table->bigInteger('parent_id');
            $table->string('path', 400);
            $table->string('name', 50);
            $table->string('permission_key', 400);
            $table->tinyInteger('is_active')->default(1)->comment("(1 => Active, 0 => Inactive) default 1");
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
        Schema::dropIfExists('lov_privileges');
    }
};
