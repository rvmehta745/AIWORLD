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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_type_id');
            $table->char('one_time_token', 36)->nullable()->unique();
            $table->boolean('is_token_used')->default(0);
            $table->string('name', 255);
            $table->string('slug', 150)->unique();
            $table->string('logo_image', 191)->nullable();
            $table->string('product_image', 191)->nullable();
            $table->text('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->string('product_url', 2048)->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->text('seo_text')->nullable();
            $table->string('extra_link1', 2048)->nullable();
            $table->string('extra_link2', 2048)->nullable();
            $table->string('extra_link3', 2048)->nullable();
            $table->text('use_case1')->nullable();
            $table->text('use_case2')->nullable();
            $table->text('use_case3')->nullable();
            $table->string('additional_info', 191)->nullable();
            $table->string('twitter', 255)->nullable();
            $table->string('facebook', 255)->nullable();
            $table->string('linkedin', 255)->nullable();
            $table->string('telegram', 255)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('payment_status', 50)->default('Pending');
            $table->string('status', 50)->default('Pending');
            $table->boolean('is_verified')->default(0);
            $table->boolean('is_gold')->default(0);
            $table->boolean('is_human_verified')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('product_type_id')
                ->references('id')->on('product_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
