<?php

namespace App\Services\V1;

use App\Repositories\V1\CategoryRepository;
use App\Services\BaseService;

class CategoryService extends BaseService
{

    private CategoryRepository $categoryRepository;
    
    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository;
    }

    /**
     * List Categories
     */
    public function list($postData, $page, $perPage)
    {
        return $this->categoryRepository->list($postData, $page, $perPage);
    }

    /**
     * Category Store
     */
    public function store($request)
    {
        return $this->categoryRepository->store($request);
    }

    /**
     * Category Details
     */
    public function details($id)
    {
        return $this->categoryRepository->details($id);
    }

    /**
     * Category Details By ID
     */
    public function detailsByID($id)
    {
        return $this->categoryRepository->detailsByID($id);
    }

    /**
     * Category Update
     */
    public function update($id, $request)
    {
        return $this->categoryRepository->update($id, $request);
    }

    /**
     * Category Delete
     */
    public function destroy($id)
    {
        return $this->categoryRepository->destroy($id);
    }

    /**
     * Category Status Change
     */
    public function changeStatus($id, $request)
    {
        return $this->categoryRepository->changeStatus($id, $request);
    }

    /**
     * Get all active categories for dropdown
     */
    public function getAllActiveCategories($productTypeId = null)
    {
        return $this->categoryRepository->getAllActiveCategories($productTypeId);
    }
} 