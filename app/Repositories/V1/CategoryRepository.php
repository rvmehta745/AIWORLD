<?php

namespace App\Repositories\V1;

use App\Models\Category;
use App\Repositories\BaseRepository;
use App\Traits\CommonTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        if ($request->hasFile('logo')) {
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
            ->select('id', 'product_type_id', 'parent_id', 'name', 'slug', 'logo', 'description', 'tools_count', 'sort_order', 'status')
            ->where('id', $id)
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
            ->select('id', 'product_type_id', 'parent_id', 'name', 'slug', 'logo', 'description', 'tools_count', 'sort_order', 'status')
            ->where('id', $id)
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

        if ($request->hasFile('logo')) {
            // delete old if exists
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
        $query = $this->category
            ->select('id', 'name', 'slug', 'parent_id', 'product_type_id')
            ->whereNull('deleted_at')
            ->where('status', 'Active')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc');

        if (!empty($productTypeId)) {
            $query->where('product_type_id', $productTypeId);
        }

        return $query->get();
    }
} 