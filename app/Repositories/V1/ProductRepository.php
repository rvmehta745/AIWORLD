<?php

namespace App\Repositories\V1;

use App\Models\Product;
use App\Repositories\BaseRepository;
use App\Traits\CommonTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductRepository extends BaseRepository
{
    use CommonTrait;

    private Product $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    /**
     * List Products
     */
    public function list($postData, $page, $perPage)
    {
        $query = DB::table('products')
            ->join('product_types', 'product_types.id', '=', 'products.product_type_id')
            ->whereNull('products.deleted_at');

        if (!empty($postData['filter_data'])) {
            foreach ($postData['filter_data'] as $key => $value) {
                if (in_array($key, [
                    'name', 'status', 'payment_status', 'is_verified', 'is_gold', 'is_human_verified'
                ])) {
                    switch ($key) {
                        case 'status':
                            $key = DB::raw('products.status');
                            break;
                        default:
                            $key = 'products.' . $key;
                            break;
                    }
                    $query = $this->createWhere('text', $key, $value, $query);
                }

                if (in_array($key, ['created_at', 'updated_at', 'published_at'])) {
                    $key   = 'products.' . $key;
                    $query = $this->createWhere('date', $key, $value, $query);
                }

                if ($key === 'product_type_id') {
                    $key = 'products.product_type_id';
                    $query = $this->createWhere('text', $key, $value, $query);
                }
            }
        }

        $query     = $query->select(
            'products.id',
            'products.product_type_id',
            'product_types.name as product_type_name',
            'products.name',
            'products.slug',
            'products.logo_image',
            'products.product_image',
            'products.short_description',
            'products.payment_status',
            'products.status',
            'products.is_verified',
            'products.is_gold',
            'products.is_human_verified',
            'products.published_at',
            'products.created_at',
            'products.updated_at',
        );
        $orderBy   = 'products.updated_at';
        $orderType = (isset($postData['order_by']) && $postData['order_by'] == 1) ? 'asc' : 'desc';
        if (!empty($postData['sort_data'])) {
            $orderBy   = $postData['sort_data'][0]['colId'];
            $orderType = $postData['sort_data'][0]['sort'];
        }
        $query       = $query->orderBy($orderBy, $orderType);
        $count       = $query->count();
        $dataPerPage = $query->skip($page)->take($perPage)->get()->toArray();

        // Map images to full URL
        foreach ($dataPerPage as $row) {
            if (!empty($row->logo_image)) {
                $row->logo_image = asset('storage/' . $row->logo_image);
            }
            if (!empty($row->product_image)) {
                // Handle JSON array of product images
                if (is_string($row->product_image)) {
                    $productImages = json_decode($row->product_image, true);
                    if (is_array($productImages)) {
                        $row->product_image = array_map(function($image) {
                            return asset('storage/' . $image);
                        }, $productImages);
                    }
                } elseif (is_array($row->product_image)) {
                    $row->product_image = array_map(function($image) {
                        return asset('storage/' . $image);
                    }, $row->product_image);
                }
            }
        }

        return ['data' => $dataPerPage, 'count' => $count];
    }

    /**
     * Store Product
     */
    public function store($request)
    {
        $storeData = [
            'product_type_id' => $request->product_type_id,
            'one_time_token' => $request->one_time_token ?? null,
            'is_token_used' => $request->is_token_used ?? 0,
            'name' => $request->name,
            'short_description' => $request->short_description ?? null,
            'long_description' => $request->long_description ?? null,
            'product_url' => $request->product_url ?? null,
            'video_url' => $request->video_url ?? null,
            'seo_text' => $request->seo_text ?? null,
            'extra_link1' => $request->extra_link1 ?? null,
            'extra_link2' => $request->extra_link2 ?? null,
            'extra_link3' => $request->extra_link3 ?? null,
            'use_case1' => $request->use_case1 ?? null,
            'use_case2' => $request->use_case2 ?? null,
            'use_case3' => $request->use_case3 ?? null,
            'use_cases' => $request->use_cases ?? null,
            'features_and_highlights' => $request->features_and_highlights ?? null,
            'additional_info' => $request->additional_info ?? null,
            'twitter' => $request->twitter ?? null,
            'facebook' => $request->facebook ?? null,
            'linkedin' => $request->linkedin ?? null,
            'telegram' => $request->telegram ?? null,
            'published_at' => $request->published_at ?? null,
            'payment_status' => $request->payment_status ?? Product::PAYMENT_STATUS['PENDING'],
            'status' => $request->status ?? 'Pending',
            'is_verified' => $request->is_verified ?? 0,
            'is_gold' => $request->is_gold ?? 0,
            'is_human_verified' => $request->is_human_verified ?? 0,
        ];

        // Handle logo image upload (single image)
        if ($request->hasFile('logo_image')) {
            $path = $request->file('logo_image')->store('product_images', 'public');
            $storeData['logo_image'] = $path;
        }
        
        // Handle product images upload (multiple images)
        if ($request->hasFile('product_image')) {
            $productImages = [];
            foreach ($request->file('product_image') as $image) {
                $path = $image->store('product_images', 'public');
                $productImages[] = $path;
            }
            $storeData['product_image'] = $productImages; // Store as JSON array
        }

        $product = Product::create($storeData);

        // Handle category assignments
        if ($request->has('category_ids') && is_array($request->category_ids)) {
            $categoryData = [];
            foreach ($request->category_ids as $categoryId) {
                $categoryData[$categoryId] = [
                    'product_type_id' => $request->product_type_id
                ];
            }
            $product->categories()->sync($categoryData);
        }

        // Handle price type assignments
        if ($request->has('price_type_ids') && is_array($request->price_type_ids)) {
            $product->priceTypes()->sync($request->price_type_ids);
        }

        return $product;
    }

    /**
     * Details Product
     */
    public function details($id)
    {
        $data = $this->product
            ->select(
                'id', 'product_type_id', 'name', 'slug', 'logo_image', 'product_image',
                'short_description', 'long_description', 'product_url', 'video_url',
                'seo_text', 'extra_link1', 'extra_link2', 'extra_link3',
                'use_case1', 'use_case2', 'use_case3', 'use_cases', 'features_and_highlights', 'additional_info',
                'twitter', 'facebook', 'linkedin', 'telegram', 'published_at',
                'payment_status', 'status', 'is_verified', 'is_gold', 'is_human_verified'
            )
            ->with('productType:id,name')
            ->where('id', $id)
            ->first();

        if ($data) {
            if (!empty($data->logo_image)) {
                $data->logo_image = asset('storage/' . $data->logo_image);
            }
            if (!empty($data->product_image)) {
                // Handle JSON array of product images
                if (is_array($data->product_image)) {
                    $data->product_image = array_map(function($image) {
                        return asset('storage/' . $image);
                    }, $data->product_image);
                }
            }
            
            // Add product type name
            if ($data->productType) {
                $data->product_type_name = $data->productType->name;
                // Remove the productType relationship from response
                unset($data->productType);
            }
        }

        return $data;
    }

    /**
     * Details Product By ID
     */
    public function detailsByID($id)
    {
        $product = $this->product
            ->select('id', 'product_type_id', 'name', 'slug', 'logo_image', 'product_image', 'short_description', 'long_description', 'product_url', 'video_url', 'seo_text', 'extra_link1', 'extra_link2', 'extra_link3', 'use_case1', 'use_case2', 'use_case3', 'use_cases', 'features_and_highlights', 'additional_info', 'twitter', 'facebook', 'linkedin', 'telegram', 'published_at', 'payment_status', 'status', 'is_verified', 'is_gold', 'is_human_verified')
            ->with([
                'productType:id,name',
                'categories' => function($query) {
                    $query->select('id', 'name');
                },
                'priceTypes' => function($query) {
                    $query->select('id', 'name');
                }
            ])
            ->where('id', $id)
            ->first();

        if ($product) {
            // Handle image URLs
            if (!empty($product->logo_image)) {
                $product->logo_image = asset('storage/' . $product->logo_image);
            }
            if (!empty($product->product_image)) {
                // Handle JSON array of product images
                if (is_array($product->product_image)) {
                    $product->product_image = array_map(function($image) {
                        return asset('storage/' . $image);
                    }, $product->product_image);
                }
            }

            // Add product type name
            if ($product->productType) {
                $product->product_type_name = $product->productType->name;
                // Remove the productType relationship from response
                unset($product->productType);
            }

            // Transform categories to remove pivot data
            if ($product->categories) {
                $product->categories->each(function($category) {
                    $category->makeHidden(['pivot']);
                });
            }

            // Transform priceTypes to remove pivot data
            if ($product->priceTypes) {
                $product->priceTypes->each(function($priceType) {
                    $priceType->makeHidden(['pivot']);
                });
            }
        }

        return $product;
    }

    /**
     * Update Product
     */
    public function update($id, $request)
    {
        $data = $this->product->find($id);

        $updateData = [
            'product_type_id' => $request->product_type_id,
            'one_time_token' => $request->one_time_token ?? $data->one_time_token,
            'is_token_used' => $request->is_token_used ?? $data->is_token_used,
            'name' => $request->name,
            'short_description' => $request->short_description ?? null,
            'long_description' => $request->long_description ?? null,
            'product_url' => $request->product_url ?? null,
            'video_url' => $request->video_url ?? null,
            'seo_text' => $request->seo_text ?? null,
            'extra_link1' => $request->extra_link1 ?? null,
            'extra_link2' => $request->extra_link2 ?? null,
            'extra_link3' => $request->extra_link3 ?? null,
            'use_case1' => $request->use_case1 ?? null,
            'use_case2' => $request->use_case2 ?? null,
            'use_case3' => $request->use_case3 ?? null,
            'use_cases' => $request->use_cases ?? null,
            'features_and_highlights' => $request->features_and_highlights ?? null,
            'additional_info' => $request->additional_info ?? null,
            'twitter' => $request->twitter ?? null,
            'facebook' => $request->facebook ?? null,
            'linkedin' => $request->linkedin ?? null,
            'telegram' => $request->telegram ?? null,
            'published_at' => $request->published_at ?? null,
            'payment_status' => $request->payment_status ?? $data->payment_status,
            'status' => $request->status ?? $data->status,
            'is_verified' => $request->is_verified ?? $data->is_verified,
            'is_gold' => $request->is_gold ?? $data->is_gold,
            'is_human_verified' => $request->is_human_verified ?? $data->is_human_verified,
        ];

        // Handle logo image upload (single image)
        if ($request->hasFile('logo_image')) {
            if (!empty($data->logo_image) && Storage::disk('public')->exists($data->logo_image)) {
                Storage::disk('public')->delete($data->logo_image);
            }
            $path = $request->file('logo_image')->store('product_images', 'public');
            $updateData['logo_image'] = $path;
        }
        
        // Handle product images upload (multiple images)
        if ($request->hasFile('product_image')) {
            // Delete existing product images
            if (!empty($data->product_image) && is_array($data->product_image)) {
                foreach ($data->product_image as $existingImage) {
                    if (Storage::disk('public')->exists($existingImage)) {
                        Storage::disk('public')->delete($existingImage);
                    }
                }
            }
            
            // Upload new product images
            $productImages = [];
            foreach ($request->file('product_image') as $image) {
                $path = $image->store('product_images', 'public');
                $productImages[] = $path;
            }
            $updateData['product_image'] = $productImages; // Store as JSON array
        }

        $data->update($updateData);

        // Handle category assignments
        if ($request->has('category_ids') && is_array($request->category_ids)) {
            $categoryData = [];
            foreach ($request->category_ids as $categoryId) {
                $categoryData[$categoryId] = [
                    'product_type_id' => $request->product_type_id
                ];
            }
            $data->categories()->sync($categoryData);
        }

        // Handle price type assignments
        if ($request->has('price_type_ids') && is_array($request->price_type_ids)) {
            $data->priceTypes()->sync($request->price_type_ids);
        }

        return $data;
    }

    /**
     * Delete Product
     */
    public function destroy($id)
    {
        $data = $this->product->find($id);
        if ($data) {
            if (!empty($data->logo_image) && Storage::disk('public')->exists($data->logo_image)) {
                Storage::disk('public')->delete($data->logo_image);
            }
            if (!empty($data->product_image) && Storage::disk('public')->exists($data->product_image)) {
                Storage::disk('public')->delete($data->product_image);
            }
            return $data->delete();
        }
        return false;
    }

    /**
     * Product Status Change
     */
    public function changeStatus($id, $request)
    {
        $data = $this->product->find($id);
        $data->update([
            'status' => $request->status,
        ]);

        return $data;
    }

    /**
     * Get all active products for dropdown (optionally by product type)
     */
    public function getAllActiveProducts($productTypeId = null)
    {
        $query = $this->product
            ->select('id', 'name', 'slug', 'product_type_id')
            ->whereNull('deleted_at')
            ->orderBy('name', 'asc');

        if (!empty($productTypeId)) {
            $query->where('product_type_id', $productTypeId);
        }

        return $query->get();
    }
}
