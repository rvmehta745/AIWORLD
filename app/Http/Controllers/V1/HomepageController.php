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
                ->with(['children' => function($query) {
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
     *     path="/parent-categories",
     *     summary="Get all parent categories",
     *     tags={"HomePage"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all parent categories",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="categories", 
     *                 type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="product_type_id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="logo", type="string", nullable=true),
     *                     @OA\Property(property="description", type="string", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getParentCategories()
    {
        try {
            // Get all parent categories (with no parent_id)
            $categories = Category::where('parent_id', null)
                ->where('status', 1)
                ->orderBy('sort_order', 'asc')
                ->get();
            
            return response()->json(['categories' => $categories], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving parent categories: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/trending-product-types",
     *     summary="Get latest trending product types",
     *     tags={"HomePage"},
     *     @OA\Response(
     *         response=200,
     *         description="List of trending product types",
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
     *                     @OA\Property(property="configuration", type="string", nullable=true),
     *                     @OA\Property(property="sort_order", type="integer"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getTrendingProductTypes()
    {
        try {
            // Get latest 10 active product types ordered by created_at
            $productTypes = ProductType::where('status', 'Active')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Add full URL for logo
            foreach ($productTypes as $productType) {
                if (!empty($productType->logo)) {
                    $productType->logo = asset('storage/' . $productType->logo);
                }
            }
            
            return response()->json(['product_types' => $productTypes], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving trending product types: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/trending-categories",
     *     summary="Get trending parent categories",
     *     tags={"HomePage"},
     *     @OA\Response(
     *         response=200,
     *         description="List of trending parent categories",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="categories", 
     *                 type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="product_type_id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="logo", type="string", nullable=true),
     *                     @OA\Property(property="description", type="string", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getTrendingCategories()
    {
        try {
            // Get 12 parent categories ordered by created_at (newest first)
            $categories = Category::where('parent_id', null)
                ->where('status', 'Active')
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
            
            // Add full URL for logo
            foreach ($categories as $category) {
                if (!empty($category->logo)) {
                    $category->logo = asset('storage/' . $category->logo);
                }
            }
            
            return response()->json(['categories' => $categories], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving trending categories: ' . $e->getMessage()], 500);
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
     *                     @OA\Property(property="product_type_id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="logo", type="string", nullable=true),
     *                     @OA\Property(property="description", type="string", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="best_resources", 
     *                 type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="tag_line", type="string", nullable=true),
     *                     @OA\Property(property="logo", type="string", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="free_ai_tools", 
     *                 type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="product_type_id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="logo", type="string", nullable=true),
     *                     @OA\Property(property="description", type="string", nullable=true),
     *                     @OA\Property(property="children", type="array", @OA\Items(type="object"))
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
            $topCategories = Category::where('parent_id', null)
                ->where('status', 'Active')
                ->orderBy('sort_order', 'asc')
                ->limit(12)
                ->get();
            
            // Add full URL for logo
            foreach ($topCategories as $category) {
                if (!empty($category->logo)) {
                    $category->logo = asset('storage/' . $category->logo);
                }
            }
            
            // Best Resources: 12 product types
            $bestResources = ProductType::where('status', 'Active')
                ->orderBy('sort_order', 'asc')
                ->limit(12)
                ->get(['id', 'name', 'slug', 'tag_line', 'logo']);
                
            // Add full URL for logo
            foreach ($bestResources as $resource) {
                if (!empty($resource->logo)) {
                    $resource->logo = asset('storage/' . $resource->logo);
                }
            }
            
            // Free AI Tools: Categories from "AI Tools" product type with children
            $aiToolsProductType = ProductType::where('slug', 'ai-tools')
                ->orWhere('name', 'AI Tools')
                ->first();
                
            $freeAiTools = [];
            if ($aiToolsProductType) {
                $freeAiTools = Category::where('product_type_id', $aiToolsProductType->id)
                    ->where('parent_id', null)
                    ->where('status', 'Active')
                    ->orderBy('sort_order', 'asc')
                    ->with(['children' => function($query) {
                        $query->where('status', 'Active')->orderBy('sort_order', 'asc');
                    }])
                    ->limit(12)
                    ->get();
                    
                // Add full URL for logo
                foreach ($freeAiTools as $tool) {
                    if (!empty($tool->logo)) {
                        $tool->logo = asset('storage/' . $tool->logo);
                    }
                    
                    foreach ($tool->children as $child) {
                        if (!empty($child->logo)) {
                            $child->logo = asset('storage/' . $child->logo);
                        }
                    }
                }
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
}