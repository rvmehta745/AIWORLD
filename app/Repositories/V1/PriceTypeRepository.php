<?php

namespace App\Repositories\V1;

use App\Models\PriceType;
use App\Repositories\BaseRepository;
use App\Traits\CommonTrait;
use Illuminate\Support\Facades\DB;

class PriceTypeRepository extends BaseRepository
{
    use CommonTrait;

    private PriceType $priceType;

    public function __construct()
    {
        $this->priceType = new PriceType();
    }

    /**
     * List Price Types
     */
    public function list($postData, $page, $perPage)
    {
        $query = DB::table('price_types')
            ->join('product_types', 'price_types.product_type_id', '=', 'product_types.id')
            ->whereNull('price_types.deleted_at')
            ->whereNull('product_types.deleted_at');

        if (!empty($postData['filter_data'])) {
            foreach ($postData['filter_data'] as $key => $value) {
                if (in_array($key, ["name", "status"])) {
                    switch ($key) {
                        case "status":
                            $key = DB::raw('price_types.status');
                            break;
                        default:
                            $key = 'price_types.' . $key;
                            break;
                    }
                    $query = $this->createWhere('text', $key, $value, $query);
                }

                if (in_array($key, ["product_type_name"])) {
                    $key = 'product_types.name';
                    $query = $this->createWhere('text', $key, $value, $query);
                }

                if (in_array($key, ["created_at", "updated_at"])) {
                    $key   = 'price_types.' . $key;
                    $query = $this->createWhere('date', $key, $value, $query);
                }
            }
        }

        $query     = $query->select(
            'price_types.id',
            'price_types.product_type_id',
            'price_types.name',
            'price_types.status',
            'price_types.created_at',
            'price_types.updated_at',
            'product_types.name as product_type_name'
        );
        $orderBy   = 'price_types.updated_at';
        $orderType = (isset($postData['order_by']) && $postData['order_by'] == 1) ? 'asc' : 'desc';
        if (!empty($postData['sort_data'])) {
            foreach ($postData['sort_data'] as $key => $value) {
                switch ($value['colId']) {
                    case "name":
                        $orderBy = 'price_types.name';
                        break;
                    case "status":
                        $orderBy = 'price_types.status';
                        break;
                    case "product_type_name":
                        $orderBy = 'product_types.name';
                        break;
                    case "created_at":
                        $orderBy = 'price_types.created_at';
                        break;
                    case "updated_at":
                        $orderBy = 'price_types.updated_at';
                        break;
                    default:
                        $orderBy = 'price_types.updated_at';
                        break;
                }
                $orderType = $value['sort'];
            }
        }

        $count = $query->count();
        $data  = $query->orderBy($orderBy, $orderType)
            ->skip($page)
            ->take($perPage)
            ->get();

        return [
            'count' => $count,
            'data'  => $data
        ];
    }

    /**
     * Price Type Store
     */
    public function store($request)
    {
        return $this->priceType->create($request->all());
    }

    /**
     * Price Type Details
     */
    public function details($id)
    {
        return $this->priceType->with('productType')->find($id);
    }

    /**
     * Price Type Details By ID
     */
    public function detailsByID($id)
    {
        return $this->priceType->with('productType')->find($id);
    }

    /**
     * Price Type Update
     */
    public function update($id, $request)
    {
        $priceType = $this->priceType->find($id);
        if ($priceType) {
            $priceType->update($request->all());
            return $priceType->fresh(['productType']);
        }
        return null;
    }

    /**
     * Price Type Delete
     */
    public function destroy($id)
    {
        $priceType = $this->priceType->find($id);
        if ($priceType) {
            return $priceType->delete();
        }
        return false;
    }

    /**
     * Price Type Status Change
     */
    public function changeStatus($id, $request)
    {
        $priceType = $this->priceType->find($id);
        if ($priceType) {
            $priceType->update(['status' => $request->status]);
            return $priceType->fresh(['productType']);
        }
        return null;
    }

    /**
     * Get all active price types for dropdown
     *
     * @return mixed
     */
    public function getAllActivePriceTypes()
    {
        return $this->priceType->with('productType')
            ->where('status', 'Active')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get price types by product type
     *
     * @param int $productTypeId
     * @return mixed
     */
    public function getPriceTypesByProductType($productTypeId)
    {
        return $this->priceType->where('product_type_id', $productTypeId)
            ->where('status', 'Active')
            ->orderBy('name', 'asc')
            ->get();
    }
}
