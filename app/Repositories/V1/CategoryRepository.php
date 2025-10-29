<?php

namespace App\Repositories\V1;

use App\Models\Category;
use App\Repositories\BaseRepository;
use App\Traits\CommonTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryRepository extends BaseRepository
{
    use CommonTrait;

    private Category $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    /**
     * List Categories
     */
    public function list($postData, $page, $perPage)
    {
        $query = DB::table('categories')
            ->join('product_types', 'product_types.id', '=', 'categories.product_type_id')
            ->leftJoin('categories as parent_categories', 'parent_categories.id', '=', 'categories.parent_id')
            ->whereNull('categories.deleted_at');

        if (!empty($postData['filter_data'])) {
            foreach ($postData['filter_data'] as $key => $value) {
                if (in_array($key, ["name", "status"])) {
                    switch ($key) {
                        case "status":
                            $key = DB::raw('categories.status');
                            break;
                        default:
                            $key = 'categories.' . $key;
                            break;
                    }
                    $query = $this->createWhere('text', $key, $value, $query);
                }

                if (in_array($key, ["created_at", "updated_at"])) {
                    $key   = 'categories.' . $key;
                    $query = $this->createWhere('date', $key, $value, $query);
                }

                if ($key === 'product_type_id') {
                    $key = 'categories.product_type_id';
                    $query = $this->createWhere('text', $key, $value, $query);
                }
            }
        }

        $query     = $query->select(
            'categories.id',
            'categories.product_type_id',
            'product_types.name as product_type_name',
            'categories.parent_id',
            DB::raw('parent_categories.name as parent_category_name'),
            'categories.name',
            'categories.slug',
            'categories.logo',
            'categories.description',
            'categories.tools_count',
            'categories.sort_order',
            'categories.status',
            'categories.created_at',
            'categories.updated_at',
        );
        $orderBy   = 'categories.updated_at';
        $orderType = (isset($postData['order_by']) && $postData['order_by'] == 1) ? 'asc' : 'desc';
        if (!empty($postData['sort_data'])) {
            $orderBy   = $postData['sort_data'][0]['colId'];
            $orderType = $postData['sort_data'][0]['sort'];
        }
        $query       = $query->orderBy($orderBy, $orderType);
        $count       = $query->count();
        $dataPerPage = $query->skip($page)->take($perPage)->get()->toArray();

        // Map logo to full URL
        foreach ($dataPerPage as $row) {
            if (!empty($row->logo)) {
                $row->logo = asset('storage/' . $row->logo);
            }
        }

        return ['data' => $dataPerPage, 'count' => $count];
    }

    /**
     * Store Category
     */
    public function store($request)
    {
        $storeData = [
            'product_type_id' => $request->product_type_id,
            'parent_id' => $request->parent_id ?? null,
            'name' => $request->name,
            'description' => $request->description ?? null,
            'tools_count' => $request->tools_count ?? 0,
            'sort_order' => $request->sort_order ?? null,
            'status' => $request->status ?? 'InActive',
        ];

        // Handle base64 logo image (like ProductType update)
        if (!empty($request->logo) && Str::startsWith($request->logo, 'data:image')) {
            $imageParts = explode(';base64,', $request->logo);
            $imageTypeAux = explode('image/', $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'png';
            $imageBase64 = base64_decode($imageParts[1]);

            $fileName = 'category_image/' . uniqid() . '.' . $imageType;
            Storage::disk('public')->put($fileName, $imageBase64);
            $storeData['logo'] = $fileName;
        } elseif ($request->hasFile('logo')) {
            // Support traditional file upload as well
            $path = $request->file('logo')->store('category_image', 'public');
            $storeData['logo'] = $path; // e.g. category_image/filename.jpg
        }

        return Category::create($storeData);
    }

    /**
     * Details Category
     */
    public function details($id)
    {
        $data = $this->category
            ->leftJoin('categories as parent_categories', 'parent_categories.id', '=', 'categories.parent_id')
            ->select(
                'categories.id',
                'categories.product_type_id',
                'categories.parent_id',
                'categories.name',
                'categories.slug',
                'categories.logo',
                'categories.description',
                'categories.tools_count',
                'categories.sort_order',
                'categories.status',
                DB::raw('parent_categories.name as parent_category_name')
            )
            ->where('categories.id', $id)
            ->first();

        if ($data && !empty($data->logo)) {
            $data->logo = asset('storage/' . $data->logo);
        }
        return $data;
    }

    /**
     * Details Category By ID
     */
    public function detailsByID($id)
    {
        $data = $this->category
            ->leftJoin('categories as parent_categories', 'parent_categories.id', '=', 'categories.parent_id')
            ->select(
                'categories.id',
                'categories.product_type_id',
                'categories.parent_id',
                'categories.name',
                'categories.slug',
                'categories.logo',
                'categories.description',
                'categories.tools_count',
                'categories.sort_order',
                'categories.status',
                DB::raw('parent_categories.name as parent_category_name')
            )
            ->where('categories.id', $id)
            ->first();

        if ($data && !empty($data->logo)) {
            $data->logo = asset('storage/' . $data->logo);
        }
        return $data;
    }

    /**
     * Update Category
     */
    public function update($id, $request)
    {
        $data = $this->category->find($id);

        $updateData = [
            'product_type_id' => $request->product_type_id,
            'parent_id' => $request->parent_id ?? null,
            'name' => $request->name,
            'description' => $request->description ?? null,
            'tools_count' => $request->tools_count ?? 0,
            'sort_order' => $request->sort_order ?? null,
            'status' => $request->status ?? 'InActive',
        ];

        // Handle base64 logo image (like ProductType update)
        if (!empty($request->logo) && Str::startsWith($request->logo, 'data:image')) {
            if (!empty($data->logo) && Storage::disk('public')->exists($data->logo)) {
                Storage::disk('public')->delete($data->logo);
            }

            $imageParts = explode(';base64,', $request->logo);
            $imageTypeAux = explode('image/', $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'png';
            $imageBase64 = base64_decode($imageParts[1]);

            $fileName = 'category_image/' . uniqid() . '.' . $imageType;
            Storage::disk('public')->put($fileName, $imageBase64);
            $updateData['logo'] = $fileName;
        } elseif ($request->hasFile('logo')) {
            // Support traditional file upload as well
            if (!empty($data->logo) && Storage::disk('public')->exists($data->logo)) {
                Storage::disk('public')->delete($data->logo);
            }
            $path = $request->file('logo')->store('category_image', 'public');
            $updateData['logo'] = $path;
        }

        $data->update($updateData);
        return $data;
    }

    /**
     * Delete Category
     */
    public function destroy($id)
    {
        $data = $this->category->find($id);
        if ($data) {
            if (!empty($data->logo) && Storage::disk('public')->exists($data->logo)) {
                Storage::disk('public')->delete($data->logo);
            }
            return $data->delete();
        }
        return false;
    }

    /**
     * Category Status Change
     */
    public function changeStatus($id, $request)
    {
        $data = $this->category->find($id);
        $data->update([
            'status' => $request->status,
        ]);

        return $data;
    }

    /**
     * Get all active categories for dropdown (optionally by product type)
     */
    public function getAllActiveCategories($productTypeId = null)
    {
        $query = DB::table('categories')
            ->join('product_types', 'product_types.id', '=', 'categories.product_type_id')
            ->leftJoin('categories as parent_categories', 'parent_categories.id', '=', 'categories.parent_id')
            ->whereNull('categories.deleted_at')
            ->where('categories.status', 'Active');

        if (!empty($productTypeId)) {
            $query->where('categories.product_type_id', $productTypeId);
        }

        $query->select(
            'categories.id',
            'categories.product_type_id',
            'product_types.name as product_type_name',
            'categories.parent_id',
            DB::raw('parent_categories.name as parent_category_name'),
            'categories.name',
            'categories.slug',
            'categories.logo',
            'categories.description',
            'categories.tools_count',
            'categories.sort_order',
            'categories.status',
            'categories.created_at',
            'categories.updated_at'
        )
        ->orderBy('categories.sort_order', 'asc')
        ->orderBy('categories.name', 'asc');

        $data = $query->get();

        // Map logo to full URL
        foreach ($data as $row) {
            if (!empty($row->logo)) {
                $row->logo = asset('storage/' . $row->logo);
            }
        }

        return $data;
    }
}
