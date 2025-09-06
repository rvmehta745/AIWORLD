<?php

namespace App\Repositories\V1;

use App\Models\CmsPage;
use App\Repositories\BaseRepository;

class CmsPageRepository extends BaseRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new CmsPage();
    }

    /**
     * Get CMS page by page name
     *
     * @param string $pageName
     * @return CmsPage|null
     */
    public function getByPageName($pageName)
    {
        
        return $this->model->where('page', $pageName)
            ->where('status', 1)
            ->first();
    }
    
    /**
     * Get CMS page by slug
     *
     * @param string $slug
     * @return CmsPage|null
     */
    public function getBySlug($slug)
    {
        return $this->model->select('id','title','slug','status','content_html')->where('slug', $slug)
            ->first();
    }

    /**
     * Get all active CMS pages
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllActive()
    {
        return $this->model->where('status', 1)
            ->orderBy('page')
            ->get();
    }
}