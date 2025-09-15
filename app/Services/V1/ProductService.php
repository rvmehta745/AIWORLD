<?php

namespace App\Services\V1;

use App\Repositories\V1\ProductRepository;
use App\Services\BaseService;

class ProductService extends BaseService
{

    private ProductRepository $productRepository;
    
    public function __construct()
    {
        $this->productRepository = new ProductRepository;
    }

    /**
     * List Products
     */
    public function list($postData, $page, $perPage)
    {
        return $this->productRepository->list($postData, $page, $perPage);
    }

    /**
     * Product Store
     */
    public function store($request)
    {
        return $this->productRepository->store($request);
    }

    /**
     * Product Details
     */
    public function details($id)
    {
        return $this->productRepository->details($id);
    }

    /**
     * Product Details By ID
     */
    public function detailsByID($id)
    {
        return $this->productRepository->detailsByID($id);
    }

    /**
     * Product Update
     */
    public function update($id, $request)
    {
        return $this->productRepository->update($id, $request);
    }

    /**
     * Product Delete
     */
    public function destroy($id)
    {
        return $this->productRepository->destroy($id);
    }

    /**
     * Product Status Change
     */
    public function changeStatus($id, $request)
    {
        return $this->productRepository->changeStatus($id, $request);
    }

    /**
     * Get all active products for dropdown
     */
    public function getAllActiveProducts($productTypeId = null)
    {
        return $this->productRepository->getAllActiveProducts($productTypeId);
    }
}
