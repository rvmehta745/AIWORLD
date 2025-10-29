<?php

namespace App\Repositories\V1;

use App\Models\ProductType;
use App\Repositories\BaseRepository;
use App\Traits\CommonTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'product_types.logo',
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

        // Map logo to full URL
        foreach ($dataPerPage as $row) {
            if (!empty($row->logo)) {
                $row->logo = asset('storage/' . $row->logo);
            }
        }

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

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('product_type_image', 'public');
            $storeData['logo'] = $path; // e.g. product_type_image/filename.jpg
        }

        return ProductType::create($storeData);
    }

    /**
     * Details Product Type
     */
    public function details($id)
    {
        $data = $this->productType
            ->select('id', 'name', 'slug', 'tag_line', 'logo', 'configuration', 'sort_order', 'status')
            ->where('id', $id)
            ->first();

        if ($data && !empty($data->logo)) {
            $data->logo = asset('storage/' . $data->logo);
        }
        return $data;
    }

    /**
     * Details Product Type By ID
     */
    public function detailsByID($id)
    {
        $data = $this->productType
            ->select('id', 'name', 'slug', 'tag_line', 'logo', 'configuration', 'sort_order', 'status')
            ->where('id', $id)
            ->first();

        if ($data && !empty($data->logo)) {
            $data->logo = asset('storage/' . $data->logo);
        }
        return $data;
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

        // ✅ Handle base64 logo image
        if (!empty($request->logo) && Str::startsWith($request->logo, 'data:image')) {

            // delete old image if exists
            if (!empty($data->logo) && Storage::disk('public')->exists($data->logo)) {
                Storage::disk('public')->delete($data->logo);
            }

            // extract base64 data
            $imageParts = explode(";base64,", $request->logo);
            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'png';
            $imageBase64 = base64_decode($imageParts[1]);

            // generate unique filename
            $fileName = 'product_type_image/' . uniqid() . '.' . $imageType;

            // store in public disk
            Storage::disk('public')->put($fileName, $imageBase64);

            $updateData['logo'] = $fileName;
        }

        $data->update($updateData);

        return $data;
    }

    /**
     * Delete Product Type
     */
    public function destroy($id)
    {
        $data = $this->productType->find($id);
        if ($data) {
            if (!empty($data->logo) && Storage::disk('public')->exists($data->logo)) {
                Storage::disk('public')->delete($data->logo);
            }
            return $data->delete();
        }
        return false;
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
        $productTypes = $this->productType
            ->select('id', 'name', 'slug', 'tag_line', 'logo')
            ->whereNull('deleted_at')
            ->where('status', 'Active')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Add full URL for logo
        foreach ($productTypes as $productType) {
            if (!empty($productType->logo)) {
                $productType->logo = asset('storage/' . $productType->logo);
            }
        }

        return $productTypes;
    }
}
