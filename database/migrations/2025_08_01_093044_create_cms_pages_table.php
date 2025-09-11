<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id()->comment('Unique identifier for the CMS page');
            $table->string('title')->comment('The display title of the CMS page (e.g., Terms & Conditions)');
            $table->string('slug')->unique()->comment('A URL-friendly unique identifier for the page (e.g., terms-and-conditions)');
            $table->longText('content_html')->comment('The full HTML content of the CMS page');
            $table->enum('status', ['draft', 'published', 'archived'])
                ->default('draft')
                ->comment('Current publication status of the page');

            // Laravel built-in timestamps + soft delete
            $table->timestamps();
            $table->softDeletes()->comment('Timestamp when the CMS page was soft deleted');

            // User tracking
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID of the user who created this CMS page');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID of the user who last updated this CMS page');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID of the user who soft deleted this CMS page');

            // Indexes
            $table->index('slug', 'idx_slug');
            $table->index('status', 'idx_status');
            $table->index('created_at', 'idx_created_at_cms');

            // Foreign key constraints
            $table->foreign('created_by', 'fk_cms_pages_created_by')
                ->references('id')->on('mst_users')
                ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('updated_by', 'fk_cms_pages_updated_by')
                ->references('id')->on('mst_users')
                ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('deleted_by', 'fk_cms_pages_deleted_by')
                ->references('id')->on('mst_users')
                ->onDelete('set null')->onUpdate('cascade');
        });

        DB::statement("ALTER TABLE `cms_pages` COMMENT = 'Table to store static CMS pages like Terms & Conditions, Privacy Policy, About Us'");
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pages');
    }
};
