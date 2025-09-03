<?php

namespace App\Services\V1;

use App\Repositories\V1\CmsPageRepository;
use App\Services\BaseService;

class CmsPageService extends BaseService
{
    private CmsPageRepository $cmsPageRepository;

    public function __construct()
    {
        $this->cmsPageRepository = new CmsPageRepository();
    }

    /**
     * Get CMS page by page name
     *
     * @param string $pageName
     * @return mixed
     */
    public function getByPageName($pageName)
    {
        return $this->cmsPageRepository->getByPageName($pageName);
    }
    
    /**
     * Get CMS page by slug
     *
     * @param string $slug
     * @return mixed
     */
    public function getBySlug($slug)
    {
        return $this->cmsPageRepository->getBySlug($slug);
    }

    /**
     * Get all active CMS pages
     *
     * @return mixed
     */
    public function getAllActive()
    {
        return $this->cmsPageRepository->getAllActive();
    }
}