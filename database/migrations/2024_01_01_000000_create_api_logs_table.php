<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id()->comment('Unique identifier for the API log entry');
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID of the user who made the API request (NULL for unauthenticated requests)');
            $table->string('http_method', 10)->comment('HTTP method used (e.g., GET, POST, PUT, DELETE)');
            $table->string('api_endpoint', 255)->comment('The API endpoint path that was called (e.g., /api/v1/users/123)');
            $table->string('ip_address', 45)->nullable()->comment('IP address from which the request originated (supports IPv6)');
            $table->string('user_agent', 512)->nullable()->comment('The User-Agent string from the client request header');
            $table->json('request_body')->nullable()->comment('The full request payload in JSON format');
            $table->smallInteger('response_status_code')->comment('HTTP status code of the API response (e.g., 200, 400, 500)');
            $table->json('response_body')->nullable()->comment('The full response payload in JSON format');
            $table->integer('duration_ms')->nullable()->comment('Duration of the API call in milliseconds');
            $table->boolean('is_error')->default(false)->comment('Flag indicating if the API call resulted in an error (status >= 400)');
            $table->text('error_message')->nullable()->comment('Specific error message if an error occurred');

            // Laravel-managed timestamps + soft delete
            $table->timestamps();   // creates created_at & updated_at (nullable by default)
            $table->softDeletes();  // creates deleted_at (nullable)

            // Extra audit fields for tracking user actions
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID of the user who initiated the action that led to this log');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID of the user who updated this log record');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID of the user who soft deleted this log record');

            // Indexes
            $table->index('user_id', 'idx_user_id_api_log');
            $table->index('api_endpoint', 'idx_api_endpoint');
            $table->index('created_at', 'idx_created_at_api_log');
            $table->index('response_status_code', 'idx_response_status_code');
            $table->index('is_error', 'idx_is_error');

            // Foreign key constraint
            $table->foreign('user_id', 'fk_api_logs_user_id')
                ->references('id')->on('mst_users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        DB::statement("ALTER TABLE `api_logs` COMMENT = 'Table to record all API calls within the system, including client platform details'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
