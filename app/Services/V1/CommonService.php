<?php

namespace App\Services\V1;

use App\Repositories\V1\CommonRepository;
use App\Services\BaseService;
use App\Services\V1\UserService;
use App\Services\V1\MyPurchaseListService;

class CommonService extends BaseService
{
    private CommonRepository $commonRepository;

    public function __construct()
    {
        $this->commonRepository = new CommonRepository;
    }

    public function roles($request)
    {
        return $this->commonRepository->roles($request);
    }

    public function user($request, $page, $perPage)
    {
        return $this->commonRepository->user($request, $page, $perPage);
    }

    public function privilegesList($request)
    {
        return $this->commonRepository->privilegesList($request);
    }

    /**
     * Get all industries for dropdown
     *
     * @return array
     */
    public function industries()
    {
        return $this->commonRepository->industries();
    }

    /**
     * Get categories by industry for dropdown
     *
     * @param int|null $industryId
     * @return array
     */
    public function categories($industryId = null)
    {
        return $this->commonRepository->categories($industryId);
    }
    public function getCategories($industryId = null)
    {
        return $this->commonRepository->getCategories($industryId);
    }

    /**
     * Get all countries for dropdown
     *
     * @return array
     */
    public function countries()
    {
        return $this->commonRepository->countries();
    }

    /**
     * Get states by country for dropdown
     *
     * @param int $countryId
     * @return array
     */
    public function states($countryId)
    {
        return $this->commonRepository->states($countryId);
    }

    /**
     * Get cities by state for dropdown
     *
     * @param int $stateId
     * @return array
     */
    public function cities($stateId)
    {
        return $this->commonRepository->cities($stateId);
    }
    /**
     * Get combined counts for dashboard
     *
     * @return array
     */
    public function getCombinedCounts()
    {
        $userService = new UserService();
        $myPurchaseListService = new MyPurchaseListService();
        
        return [
            'total_users' => $userService->getTotalUserCount(),
            'active_users' => $userService->getActiveUserCount(),
            'total_purchase_lists' => $myPurchaseListService->getTotalCount()
        ];
    }
    
    /**
     * Get all cities for dropdown (only id and name)
     *
     * @return array
     */
    public function allCities()
    {
        return $this->commonRepository->allCities();
    }
    
    /**
     * Get all employee range for dropdown (only id and name)
     *
     * @return array
     */
    public function allEmpRange()
    {
        return $this->commonRepository->allEmpRange();
    }

    /**
     * Get all sales volumes for dropdown (only id and name)
     *
     * @return array
     */
    public function allSalesVolumes()
    {
        return $this->commonRepository->allSalesVolumes();
    }

    /**
     * Get all job titles for dropdown (only id and name)
     *
     * @return array
     */
    public function allJobTitles()
    {
        return $this->commonRepository->allJobTitles();
    }
}
