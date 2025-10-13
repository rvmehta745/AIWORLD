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
     *     path="/masterhome",
     *     summary="Get master homepage data (product types with categories and price types)",
     *     tags={"HomePage"},
     *     @OA\Response(
     *         response=200,
     *         description="List of product types with their categories, children, and price types",
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
     *                         property="price_types",
     *                         type="array",
     *                         @OA\Items(type="object",
     *                             @OA\Property(property="product_type_id", type="integer"),
     *                             @OA\Property(property="product_type_name", type="string"),
     *                             @OA\Property(property="name", type="string"),
     *                         )
     *                     ),
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
                // Add price types for this product type, only include id, product_type_id, product_type_name, name
                $productType->price_types = $productType->priceTypes()->where('status', 'Active')->get()->map(function ($pt) use ($productType) {
                    return [
                        'product_type_id' => $productType->id,
                        'product_type_name' => $productType->name,
                        'name' => $pt->name,
                    ];
                });
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

    /**
     * POST: /homepage/products-by-type-filter
     * Accepts product_type_id with either price_type_id or category_id.
     * Returns all matching products.
     */
    /**
     * @OA\Post(
     *     path="/products-by-type-filter",
     *     summary="Get all products by product type and either price type or category",
     *     tags={"HomePage"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"product_type_id"},
     *                 @OA\Property(property="product_type_id", type="integer", description="Product type ID", example=1),
     *                 @OA\Property(property="price_type_id", type="integer", description="Price type ID (optional, mutually exclusive with category_id)", example=2),
     *                 @OA\Property(property="category_id", type="integer", description="Category ID (optional, mutually exclusive with price_type_id)", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="logo_image", type="string", nullable=true),
     *                     @OA\Property(property="short_description", type="string", nullable=true),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Missing required data or invalid input"
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function productsByTypeFilter(Request $request)
    {
        $productTypeId = $request->input('product_type_id');
        $priceTypeId = $request->input('price_type_id');
        $categoryId = $request->input('category_id');

        if (!$productTypeId || (!$priceTypeId && !$categoryId)) {
            return response()->json(['message' => 'product_type_id and either price_type_id or category_id are required.'], 400);
        }

        $query = \App\Models\Product::where('product_type_id', $productTypeId)
            ->where('status', 'Active');

        if ($priceTypeId) {
            $query->whereHas('priceTypes', function ($q) use ($priceTypeId) {
                $q->where('price_types.id', $priceTypeId);
            });
        }
        if ($categoryId) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        $products = $query->get();

        $productsArr = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'logo_image' => $p->logo_image ? asset('storage/' . $p->logo_image) : null,
                'short_description' => $p->short_description,
            ];
        });

        return response()->json(['products' => $productsArr], 200);
    }

    /**
     * @OA\Post(
     *     path="/products-by-type-featured",
     *     summary="Get featured/special products by type and feature",
     *     tags={"HomePage"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"product_type_id", "type"},
     *                 @OA\Property(property="product_type_id", type="integer", description="Product type ID", example=1),
     *                 @OA\Property(property="type", type="string", enum={"Featured", "Editorpick", "Super"}, description="Type of special products", example="Featured")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products for the specified type",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="logo_image", type="string", nullable=true),
     *                     @OA\Property(property="short_description", type="string", nullable=true),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Missing required data or invalid input"
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function productsByTypeSpecial(Request $request)
    {
        $productTypeId = $request->input('product_type_id');
        $type = $request->input('type');
        if (!$productTypeId || !$type) {
            return response()->json(['message' => 'product_type_id and type are required.'], 400);
        }
        if (!in_array($type, ['Featured', 'Editorpick', 'Super'])) {
            return response()->json(['message' => 'Invalid type: must be Featured, Editorpick, or Super.'], 400);
        }
        if ($type === 'Editorpick' || $type === 'Super') {
            return response()->json(['products' => []], 200);
        }
        // Featured logic
        $products = \App\Models\Product::where('product_type_id', $productTypeId)
            ->where('status', 'Active')
            ->whereHas('featuredProducts', function ($q) {
                // Just confirming the presence in relation, no additional filter for now
            })
            ->limit(10)
            ->get();
        $productsArr = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'logo_image' => $p->logo_image ? asset('storage/' . $p->logo_image) : null,
                'short_description' => $p->short_description,
            ];
        });
        return response()->json(['products' => $productsArr], 200);
    }

    /**
     * @OA\Get(
     *     path="/product-details/{identifier}",
     *     summary="Get complete details for a product by ID or slug (with type, price types, categories, sub-categories)",
     *     tags={"HomePage"},
     *     @OA\Parameter(
     *         name="identifier",
     *         in="path",
     *         required=true,
     *         description="Product ID or unique slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="slug", type="string"),
     *                 @OA\Property(property="logo_image", type="string", nullable=true),
     *                 @OA\Property(property="product_image", type="string", nullable=true),
     *                 @OA\Property(property="short_description", type="string"),
     *                 @OA\Property(property="long_description", type="string"),
     *                 @OA\Property(property="product_url", type="string"),
     *                 @OA\Property(property="video_url", type="string", nullable=true),
     *                 @OA\Property(property="seo_text", type="string", nullable=true),
     *                 @OA\Property(property="features_and_highlights", type="string", nullable=true),
     *                 @OA\Property(property="use_cases", type="string", nullable=true),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="sort_order", type="integer"),
     *                 @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="product_type_id", type="integer"),
     *                 @OA\Property(property="productType", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string")
     *                 ),
     *                 @OA\Property(property="priceTypes", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(property="categories", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="slug", type="string"),
     *                         @OA\Property(property="logo", type="string", nullable=true),
     *                         @OA\Property(property="description", type="string", nullable=true),
     *                         @OA\Property(property="parent_id", type="integer", nullable=true),
     *                         @OA\Property(property="children", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="integer"),
     *                                 @OA\Property(property="name", type="string"),
     *                                 @OA\Property(property="slug", type="string"),
     *                                 @OA\Property(property="logo", type="string", nullable=true),
     *                                 @OA\Property(property="description", type="string", nullable=true),
     *                                 @OA\Property(property="parent_id", type="integer", nullable=true)
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found or invalid identifier"
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function productDetails($identifier)
    {
        $product = is_numeric($identifier)
            ? \App\Models\Product::with(['productType', 'priceTypes', 'categories.children'])->find($identifier)
            : \App\Models\Product::with(['productType', 'priceTypes', 'categories.children'])->where('slug', $identifier)->first();
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        // Add logo/product image URL if necessary
        if ($product->logo_image) {
            $product->logo_image = asset('storage/' . $product->logo_image);
        }
        // You can continue to add/transform any other fields here if needed
        return response()->json(['product' => $product], 200);
    }
}
