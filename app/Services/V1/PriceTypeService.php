<?php

namespace App\Services\V1;

use App\Repositories\V1\PriceTypeRepository;
use App\Services\BaseService;

class PriceTypeService extends BaseService
{

    private PriceTypeRepository $priceTypeRepository;
    
    public function __construct()
    {
        $this->priceTypeRepository = new PriceTypeRepository;
    }

    /**
     * List Price Types
     */
    public function list($postData, $page, $perPage)
    {
        return $this->priceTypeRepository->list($postData, $page, $perPage);
    }

    /**
     * Price Type Store
     */
    public function store($request)
    {
        return $this->priceTypeRepository->store($request);
    }

    /**
     * Price Type Details
     */
    public function details($id)
    {
        return $this->priceTypeRepository->details($id);
    }

    /**
     * Price Type Details By ID
     */
    public function detailsByID($id)
    {
        return $this->priceTypeRepository->detailsByID($id);
    }

    /**
     * Price Type Update
     */
    public function update($id, $request)
    {
        return $this->priceTypeRepository->update($id, $request);
    }

    /**
     * Price Type Delete
     */
    public function destroy($id)
    {
        return $this->priceTypeRepository->destroy($id);
    }

    /**
     * Price Type Status Change
     */
    public function changeStatus($id, $request)
    {
        return $this->priceTypeRepository->changeStatus($id, $request);
    }

    /**
     * Get all active price types for dropdown
     *
     * @return mixed
     */
    public function getAllActivePriceTypes()
    {
        return $this->priceTypeRepository->getAllActivePriceTypes();
    }

    /**
     * Get price types by product type
     *
     * @param int $productTypeId
     * @return mixed
     */
    public function getPriceTypesByProductType($productTypeId)
    {
        return $this->priceTypeRepository->getPriceTypesByProductType($productTypeId);
    }
}
