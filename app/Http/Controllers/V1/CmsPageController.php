<?php

namespace App\Http\Controllers\V1;

use App\Library\General;
use App\Services\V1\CmsPageService;
use Throwable;
use Illuminate\Http\Request;

class CmsPageController extends BaseController
{
    protected $cmsPageService;

    public function __construct(CmsPageService $cmsPageService)
    {
        
        $this->cmsPageService = $cmsPageService;
    }

   
    public function getByPageName($page)
    {
        try {
            $page = urldecode($page);
            $cmsPage = $this->cmsPageService->getByPageName($page);
            
            if (!$cmsPage) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => 'CMS Page']));
            }
            
            return General::setResponse("SUCCESS", [], compact('cmsPage'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     * path="/admin/cms-page/{slug}",
     * tags={"CMS Pages"},
     * summary="Get CMS page content by slug",
     * description="Returns the content of a specific CMS page using its slug",
     * operationId="cms-page-by-slug",
     *      @OA\Parameter(
     *          name="slug",
     *          in="path",
     *          required=true,
     *          description="URL-friendly page slug (e.g., 'terms-of-use', 'privacy-policy', 'refund-policy')",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      ),
     * )
     */
    public function getBySlug($slug)
    {
        try {
            $cmsPage = $this->cmsPageService->getBySlug($slug);
            
            if (!$cmsPage) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => 'CMS Page']));
            }
            
            return General::setResponse("SUCCESS", [], compact('cmsPage'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    
    public function getAllActive()
    {
        try {
            $cmsPages = $this->cmsPageService->getAllActive();
            return General::setResponse("SUCCESS", [], compact('cmsPages'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }
}