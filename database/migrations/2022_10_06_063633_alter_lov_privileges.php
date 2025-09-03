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
        Schema::table('lov_privileges', function (Blueprint $table) {
            $table->bigInteger('group_id')->unsigned()->change();
            $table->bigInteger('parent_id')->unsigned()->change();
            $table->foreign('group_id')->references('id')->on('lov_privilege_groups')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('parent_id')->references('id')->on('lov_privileges')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lov_privileges', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropForeign(['parent_id']);
            // $table->bigInteger('group_id')->change();
            // $table->bigInteger('parent_id')->change();
        });
    }
};
