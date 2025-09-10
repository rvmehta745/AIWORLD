<?php

namespace App\Services\V1;

use App\Repositories\V1\ProductTypeRepository;
use App\Services\BaseService;

class ProductTypeService extends BaseService
{

    private ProductTypeRepository $productTypeRepository;
    
    public function __construct()
    {
        $this->productTypeRepository = new ProductTypeRepository;
    }

    /**
     * List Product Types
     */
    public function list($postData, $page, $perPage)
    {
        return $this->productTypeRepository->list($postData, $page, $perPage);
    }

    /**
     * Product Type Store
     */
    public function store($request)
    {
        return $this->productTypeRepository->store($request);
    }

    /**
     * Product Type Details
     */
    public function details($id)
    {
        return $this->productTypeRepository->details($id);
    }

    /**
     * Product Type Details By ID
     */
    public function detailsByID($id)
    {
        return $this->productTypeRepository->detailsByID($id);
    }

    /**
     * Product Type Update
     */
    public function update($id, $request)
    {
        return $this->productTypeRepository->update($id, $request);
    }

    /**
     * Product Type Delete
     */
    public function destroy($id)
    {
        return $this->productTypeRepository->destroy($id);
    }

    /**
     * Product Type Status Change
     */
    public function changeStatus($id, $request)
    {
        return $this->productTypeRepository->changeStatus($id, $request);
    }

    /**
     * Get all active product types for dropdown
     *
     * @return mixed
     */
    public function getAllActiveProductTypes()
    {
        return $this->productTypeRepository->getAllActiveProductTypes();
    }
}
