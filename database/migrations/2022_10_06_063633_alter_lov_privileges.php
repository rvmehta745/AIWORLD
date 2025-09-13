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
        Schema::table('lov_privileges', function (Blueprint $table) {
            $table->bigInteger('group_id')->unsigned()->change();
            $table->bigInteger('parent_id')->unsigned()->change();
            $table->foreign('group_id', 'fk_lov_privileges_group_id')->references('id')->on('lov_privilege_groups')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('parent_id', 'fk_lov_privileges_parent_id')->references('id')->on('lov_privileges')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lov_privileges', function (Blueprint $table) {
            $table->dropForeign('fk_lov_privileges_group_id');
            $table->dropForeign('fk_lov_privileges_parent_id');
            $table->bigInteger('group_id')->change();
            $table->bigInteger('parent_id')->change();
        });
    }
};
