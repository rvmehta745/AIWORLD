<?php

namespace App\Repositories\V1;

use App\Models\ProductType;
use App\Repositories\BaseRepository;
use App\Traits\CommonTrait;
use Illuminate\Support\Facades\DB;

class ProductTypeRepository extends BaseRepository
{
    use CommonTrait;

    private ProductType $productType;

    public function __construct()
    {
        $this->productType = new ProductType();
    }

    /**
     * List Product Types
     */
    public function list($postData, $page, $perPage)
    {
        $query = DB::table('product_types')
            ->whereNull('product_types.deleted_at');

        if (!empty($postData['filter_data'])) {
            foreach ($postData['filter_data'] as $key => $value) {
                if (in_array($key, ["name", "tag_line", "status"])) {
                    switch ($key) {
                        case "status":
                            $key = DB::raw('product_types.status');
                            break;
                        default:
                            $key = 'product_types.' . $key;
                            break;
                    }
                    $query = $this->createWhere('text', $key, $value, $query);
                }

                if (in_array($key, ["created_at", "updated_at"])) {
                    $key   = 'product_types.' . $key;
                    $query = $this->createWhere('date', $key, $value, $query);
                }
            }
        }

        $query     = $query->select(
            'product_types.id',
            'product_types.name',
            'product_types.slug',
            'product_types.tag_line',
            'product_types.configuration',
            'product_types.sort_order',
            'product_types.status',
            'product_types.created_at',
            'product_types.updated_at',
        );
        $orderBy   = 'product_types.updated_at';
        $orderType = (isset($postData['order_by']) && $postData['order_by'] == 1) ? 'asc' : 'desc';
        if (!empty($postData['sort_data'])) {
            $orderBy   = $postData['sort_data'][0]['colId'];
            $orderType = $postData['sort_data'][0]['sort'];
        }
        $query       = $query->orderBy($orderBy, $orderType);
        $count       = $query->count();
        $dataPerPage = $query->skip($page)->take($perPage)->get()->toArray();
        return ['data' => $dataPerPage, 'count' => $count];
    }

    /**
     * Store Product Type
     */
    public function store($request)
    {
        $storeData = [
            'name' => $request->name,
            'tag_line' => $request->tag_line ?? null,
            'configuration' => $request->configuration ?? null,
            'sort_order' => $request->sort_order ?? null,
            'status' => $request->status ?? 'InActive',
        ];

        return ProductType::create($storeData);
    }

    /**
     * Details Product Type
     */
    public function details($id)
    {
        return $this->productType
            ->select('id', 'name', 'slug', 'tag_line', 'configuration', 'sort_order', 'status')
            ->where('id', $id)
            ->first();
    }

    /**
     * Details Product Type By ID
     */
    public function detailsByID($id)
    {
        return $this->productType
            ->select('id', 'name', 'slug', 'tag_line', 'configuration', 'sort_order', 'status')
            ->where('id', $id)
            ->first();
    }

    /**
     * Update Product Type
     */
    public function update($id, $request)
    {
        $data = $this->productType->find($id);

        $updateData = [
            'name' => $request->name,
            'tag_line' => $request->tag_line ?? null,
            'configuration' => $request->configuration ?? null,
            'sort_order' => $request->sort_order ?? null,
            'status' => $request->status ?? 'InActive',
        ];

        $data->update($updateData);
        return $data;
    }

    /**
     * Delete Product Type
     */
    public function destroy($id)
    {
        return $this->productType->find($id)->delete();
    }

    /**
     * Product Type Status Change
     */
    public function changeStatus($id, $request)
    {
        $data = $this->productType->find($id);
        $data->update([
            'status' => $request->status,
        ]);

        return $data;
    }

    /**
     * Get all active product types for dropdown
     *
     * @return mixed
     */
    public function getAllActiveProductTypes()
    {
        return $this->productType
            ->select('id', 'name', 'slug', 'tag_line')
            ->whereNull('deleted_at')
            ->where('status', 'Active')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }
}
