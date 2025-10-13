<?php

namespace App\Http\Controllers\V1;

use App\Models\Setting;
use App\Models\Category;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomepageController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/menu",
     *     summary="Get menu JSON for frontend",
     *     tags={"HomePage"},
     *     @OA\Response(
     *         response=200,
     *         description="Menu JSON",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="menu", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=404, description="Menu not found")
     * )
     */
    public function getMenu()
    {
        $setting = Setting::where('key', 'menu')->first();
        if (!$setting) {
            return response()->json(['message' => 'Menu not found'], 404);
        }
        return response()->json($setting->value, 200);
    }

    /**
     * @OA\Get(
     *     path="/categories/{identifier}",
     *     summary="Get categories by product type",
     *     tags={"HomePage"},
     *     @OA\Parameter(
     *         name="identifier",
     *         in="path",
     *         required=true,
     *         description="Product type ID or slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories with child categories",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="categories", 
     *                 type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="logo", type="string", nullable=true),
     *                     @OA\Property(property="description", type="string", nullable=true),
     *                     @OA\Property(property="children", type="array", @OA\Items(type="object"))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product type not found")
     * )
     */
    public function getCategories($identifier)
    {
        try {
            // Find product type by ID or slug
            $productType = is_numeric($identifier)
                ? ProductType::find($identifier)
                : ProductType::where('slug', $identifier)->first();

            if (!$productType) {
                return response()->json(['message' => 'Product type not found'], 404);
            }

            // Get parent categories (with no parent_id) for this product type
            $categories = Category::where('product_type_id', $productType->id)
                ->where('parent_id', null)
                ->where('status', 1)
                ->orderBy('sort_order', 'asc')
                ->with(['children' => function ($query) {
                    $query->where('status', 1)->orderBy('sort_order', 'asc');
                }])
                ->get();

            return response()->json(['categories' => $categories], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving categories: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/footer",
     *     summary="Get footer data for frontend",
     *     tags={"HomePage"},
     *     @OA\Response(
     *         response=200,
     *         description="Footer data containing top categories, best resources, and free AI tools",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="top_categories", 
     *                 type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="best_resources", 
     *                 type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="free_ai_tools", 
     *                 type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getFooter()
    {
        try {
            // Top categories: 12 parent categories
            $topCategories = Category::whereNull('parent_id')
                ->where('status', 'Active')
                ->orderBy('sort_order', 'asc')
                ->limit(12)
                ->get(['id', 'name', 'slug']);

            // Best Resources: 12 product types
            $bestResources = ProductType::where('status', 'Active')
                ->orderBy('sort_order', 'asc')
                ->limit(12)
                ->get(['id', 'name', 'slug']);

            // Free AI Tools: Parent categories from "AI Tools" product type
            $freeAiTools = [];
            $aiToolsProductType = ProductType::where('slug', 'ai-tools')
                ->orWhere('name', 'AI Tools')
                ->first();

            if ($aiToolsProductType) {
                $freeAiTools = Category::where('product_type_id', $aiToolsProductType->id)
                    ->whereNull('parent_id')
                    ->where('status', 'Active')
                    ->orderBy('sort_order', 'asc')
                    ->limit(12)
                    ->get(['id', 'name', 'slug']);
            }

            return response()->json([
                'top_categories' => $topCategories,
                'best_resources' => $bestResources,
                'free_ai_tools' => $freeAiTools
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving footer data: ' . $e->getMessage()], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/master-home",
     *     summary="Get master homepage data (product types with categories)",
     *     tags={"HomePage"},
     *     @OA\Response(
     *         response=200,
     *         description="List of product types with their categories and children",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="product_types", 
     *                 type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="tag_line", type="string", nullable=true),
     *                     @OA\Property(property="logo", type="string", nullable=true),
     *                     @OA\Property(
     *                         property="categories",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="product_type_id", type="integer"),
     *                             @OA\Property(property="product_type_name", type="string"),
     *                             @OA\Property(property="parent_id", type="integer", nullable=true),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="slug", type="string"),
     *                             @OA\Property(property="logo", type="string", nullable=true),
     *                             @OA\Property(property="description", type="string", nullable=true),
     *                             @OA\Property(
     *                                 property="children",
     *                                 type="array",
     *                                 @OA\Items(
     *                                     type="object",
     *                                     @OA\Property(property="id", type="integer"),
     *                                     @OA\Property(property="product_type_id", type="integer"),
     *                                     @OA\Property(property="product_type_name", type="string"),
     *                                     @OA\Property(property="parent_id", type="integer", nullable=true),
     *                                     @OA\Property(property="name", type="string"),
     *                                     @OA\Property(property="slug", type="string"),
     *                                     @OA\Property(property="logo", type="string", nullable=true),
     *                                     @OA\Property(property="description", type="string", nullable=true)
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getMasterHome()
    {
        try {
            // Fetch all active product types
            $productTypes = ProductType::where('status', 'Active')
                ->orderBy('sort_order', 'asc')
                ->get(['id', 'name', 'slug', 'tag_line', 'logo']);

            foreach ($productTypes as $productType) {
                // Add full logo URL if exists
                if (!empty($productType->logo)) {
                    $productType->logo = asset('storage/' . $productType->logo);
                }

                // Fetch all parent categories for this product type
                $categories = Category::where('product_type_id', $productType->id)
                    ->whereNull('parent_id')
                    ->where('status', 'Active')
                    ->orderBy('sort_order', 'asc')
                    ->get(['id', 'product_type_id', 'name', 'slug', 'logo', 'description', 'parent_id']);

                // Add full logo URL + product type name
                foreach ($categories as $category) {
                    $category->product_type_name = $productType->name;
                    if (!empty($category->logo)) {
                        $category->logo = asset('storage/' . $category->logo);
                    }

                    // Fetch active children for this category
                    $children = Category::where('parent_id', $category->id)
                        ->where('status', 'Active')
                        ->orderBy('sort_order', 'asc')
                        ->get(['id', 'product_type_id', 'name', 'slug', 'logo', 'description', 'parent_id']);

                    foreach ($children as $child) {
                        $child->product_type_name = $productType->name;
                        if (!empty($child->logo)) {
                            $child->logo = asset('storage/' . $child->logo);
                        }
                    }

                    $category->children = $children;
                }

                $productType->categories = $categories;
            }

            return response()->json(['product_types' => $productTypes], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving master home data: ' . $e->getMessage()], 500);
        }
    }
}
