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
     * Get combined counts for dashboard
     *
     * @return array
     */
    public function getCombinedCounts()
    {
        $userService = new UserService();
        
        return [
            'total_users' => $userService->getTotalUserCount(),
            'active_users' => $userService->getActiveUserCount(),
            'total_purchase_lists' => 0 // Placeholder - service not available
        ];
    }
    
    
}
