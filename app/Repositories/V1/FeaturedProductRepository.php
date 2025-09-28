<?php

namespace App\Repositories\V1;

use App\Models\FeaturedProduct;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class FeaturedProductRepository extends BaseRepository
{
    protected $model;

    public function __construct(FeaturedProduct $model)
    {
        $this->model = $model;
    }

    public function list($postData, $skip, $pageLimit)
    {
        $sortData = $postData['sort_data'] ?? [['colId' => 'id', 'sort' => 'desc']];
        $filterData = $postData['filter_data'] ?? [];

        $query = $this->model->with(['productType', 'products']);

        // Apply filters
        if (!empty($filterData)) {
            foreach ($filterData as $key => $filter) {
                if (isset($filter['filterType']) && $filter['filterType'] === 'text' && !empty($filter['filter'])) {
                    switch ($key) {
                        case 'product_type':
                            $query->whereHas('productType', function ($q) use ($filter) {
                                $q->where('name', 'like', '%' . $filter['filter'] . '%');
                            });
                            break;
                        case 'featured_url':
                            $query->where('featured_url', 'like', '%' . $filter['filter'] . '%');
                            break;
                    }
                }

                if (isset($filter['filterType']) && $filter['filterType'] === 'date' && !empty($filter['dateFrom'])) {
                    switch ($key) {
                        case 'start_date':
                            $query->whereDate('start_date', '>=', $filter['dateFrom']);
                            if (!empty($filter['dateTo'])) {
                                $query->whereDate('start_date', '<=', $filter['dateTo']);
                            }
                            break;
                        case 'end_date':
                            $query->whereDate('end_date', '>=', $filter['dateFrom']);
                            if (!empty($filter['dateTo'])) {
                                $query->whereDate('end_date', '<=', $filter['dateTo']);
                            }
                            break;
                        case 'created_at':
                            $query->whereDate('created_at', '>=', $filter['dateFrom']);
                            if (!empty($filter['dateTo'])) {
                                $query->whereDate('created_at', '<=', $filter['dateTo']);
                            }
                            break;
                    }
                }
            }
        }

        // Apply sorting
        foreach ($sortData as $sort) {
            if (isset($sort['colId']) && isset($sort['sort'])) {
                $column = $sort['colId'];
                $direction = $sort['sort'] === 'asc' ? 'asc' : 'desc';
                
                switch ($column) {
                    case 'product_type':
                        $query->join('product_types', 'featured_products.product_type_id', '=', 'product_types.id')
                              ->orderBy('product_types.name', $direction)
                              ->select('featured_products.*');
                        break;
                    default:
                        $query->orderBy($column, $direction);
                        break;
                }
            }
        }

        $totalCount = $query->count();
        
        $query->skip($skip)->take($pageLimit);
        $data = $query->get();

        return [
            'count' => $totalCount,
            'data' => $data
        ];
    }

    public function store($data)
    {
        return $this->model->create($data);
    }

    public function details($id)
    {
        return $this->model->with(['productType', 'products'])->find($id);
    }

    public function detailsByID($id)
    {
        return $this->model->with(['productType', 'products'])->find($id);
    }

    public function update($id, $data)
    {
        $featuredProduct = $this->model->find($id);
        if ($featuredProduct) {
            $featuredProduct->update($data);
            return $featuredProduct;
        }
        return null;
    }

    public function destroy($id)
    {
        $featuredProduct = $this->model->find($id);
        if ($featuredProduct) {
            // Delete associated products relationship
            $featuredProduct->products()->detach();
            return $featuredProduct->delete();
        }
        return false;
    }

    public function getActiveFeaturedProducts($productTypeId = null)
    {
        $query = $this->model->with(['productType', 'products'])
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('sort_order', 'asc');

        if ($productTypeId) {
            $query->where('product_type_id', $productTypeId);
        }

        return $query->get();
    }

    public function getNextSortOrder($productTypeId = null)
    {
        $query = $this->model->query();
        
        if ($productTypeId) {
            $query->where('product_type_id', $productTypeId);
        }
        
        $maxSortOrder = $query->max('sort_order') ?? 0;
        return $maxSortOrder + 1;
    }
} 