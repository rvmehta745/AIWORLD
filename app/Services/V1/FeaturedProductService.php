<?php

namespace App\Services\V1;

use App\Repositories\V1\FeaturedProductRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;

class FeaturedProductService extends BaseService
{
    private FeaturedProductRepository $featuredProductRepository;

    public function __construct(FeaturedProductRepository $featuredProductRepository)
    {
        $this->featuredProductRepository = $featuredProductRepository;
    }

    public function list($postData, $skip, $pageLimit)
    {
        return $this->featuredProductRepository->list($postData, $skip, $pageLimit);
    }

    public function store($request)
    {
        $data = $request->validated();
        
        // Set created_by and sort_order if not provided
        $data['created_by'] = Auth::id();
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = $this->featuredProductRepository->getNextSortOrder($data['product_type_id']);
        }

        // Extract product_ids for later use
        $productIds = $data['product_ids'];
        unset($data['product_ids']);

        // Create the featured product
        $featuredProduct = $this->featuredProductRepository->store($data);

        // Attach products to the featured product
        if ($featuredProduct && !empty($productIds)) {
            $featuredProduct->products()->attach($productIds);
        }

        return $featuredProduct;
    }

    public function details($id)
    {
        return $this->featuredProductRepository->details($id);
    }

    public function detailsByID($id)
    {
        return $this->featuredProductRepository->detailsByID($id);
    }

    public function update($id, $request)
    {
        $data = $request->validated();
        
        // Set updated_by
        $data['updated_by'] = Auth::id();

        // Extract product_ids for later use
        $productIds = $data['product_ids'];
        unset($data['product_ids']);

        // Update the featured product
        $featuredProduct = $this->featuredProductRepository->update($id, $data);

        // Sync products with the featured product
        if ($featuredProduct && !empty($productIds)) {
            $featuredProduct->products()->sync($productIds);
        }

        return $featuredProduct;
    }

    public function destroy($id)
    {
        $featuredProduct = $this->featuredProductRepository->details($id);
        if ($featuredProduct) {
            // Set deleted_by before soft delete
            $featuredProduct->update(['deleted_by' => Auth::id()]);
            return $this->featuredProductRepository->destroy($id);
        }
        return false;
    }

    public function getActiveFeaturedProducts($productTypeId = null)
    {
        return $this->featuredProductRepository->getActiveFeaturedProducts($productTypeId);
    }

    public function reorder($featuredProductId, $oldPosition, $newPosition)
    {
        if ($oldPosition == $newPosition) {
            return false;
        }

        // Get the featured product
        $featuredProduct = $this->featuredProductRepository->details($featuredProductId);
        if (!$featuredProduct) {
            return false;
        }

        if ($oldPosition < $newPosition) {
            // Shift UP (dragging downwards)
            // Move featured products between old_position+1 and new_position down by 1
            \App\Models\FeaturedProduct::whereBetween('sort_order', [$oldPosition + 1, $newPosition])
                ->decrement('sort_order');
        } else {
            // Shift DOWN (dragging upwards)
            // Move featured products between new_position and old_position-1 up by 1
            \App\Models\FeaturedProduct::whereBetween('sort_order', [$newPosition, $oldPosition - 1])
                ->increment('sort_order');
        }

        // Set new position for dragged featured product
        $featuredProduct->sort_order = $newPosition;
        $featuredProduct->updated_by = Auth::id();
        $featuredProduct->save();

        return true;
    }
} 