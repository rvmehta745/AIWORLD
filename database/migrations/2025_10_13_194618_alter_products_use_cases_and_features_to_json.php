<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, convert existing data to JSON array string if not already
        DB::table('products')->select(['id', 'use_cases', 'features_and_highlights'])->orderBy('id')->chunk(100, function($products) {
            foreach ($products as $product) {
                $update = [];
                // Convert use_cases
                if ($product->use_cases !== null && trim($product->use_cases) !== '') {
                    // Split by lines or commas if array-like, or wrap as array
                    $uc = array_filter(preg_split('/\r\n|\r|\n|,/', $product->use_cases));
                    $update['use_cases'] = json_encode(array_map('trim', $uc));
                } else {
                    $update['use_cases'] = json_encode([]);
                }
                // Convert features_and_highlights
                if ($product->features_and_highlights !== null && trim($product->features_and_highlights) !== '') {
                    $fh = array_filter(preg_split('/\r\n|\r|\n|,/', $product->features_and_highlights));
                    $update['features_and_highlights'] = json_encode(array_map('trim', $fh));
                } else {
                    $update['features_and_highlights'] = json_encode([]);
                }
                DB::table('products')->where('id', $product->id)->update($update);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('use_cases')->nullable()->change();
            $table->json('features_and_highlights')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('use_cases')->nullable()->change();
            $table->text('features_and_highlights')->nullable()->change();
        });

        // Optionally convert back to text (string join)
        DB::table('products')->select(['id', 'use_cases', 'features_and_highlights'])->orderBy('id')->chunk(100, function($products) {
            foreach ($products as $product) {
                $update = [];
                // use_cases
                if ($product->use_cases !== null) {
                    $arr = json_decode($product->use_cases, true);
                    $update['use_cases'] = is_array($arr) ? implode("\n", $arr) : $product->use_cases;
                }
                // features_and_highlights
                if ($product->features_and_highlights !== null) {
                    $arr = json_decode($product->features_and_highlights, true);
                    $update['features_and_highlights'] = is_array($arr) ? implode("\n", $arr) : $product->features_and_highlights;
                }
                DB::table('products')->where('id', $product->id)->update($update);
            }
        });
    }
};
