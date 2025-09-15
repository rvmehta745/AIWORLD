<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Requests\V1\Product\StoreProductRequest;
use App\Http\Requests\V1\Product\UpdateProductRequest;
use App\Services\V1\ProductService;
use Illuminate\Http\Request;
use App\Library\General;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API endpoints for managing Products"
 * )
 */
class ProductController extends \App\Http\Controllers\V1\BaseController
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @OA\Post(
     * path="/products",
     * tags = {"Products"},
     * summary = "Get list of Products",
     * operationId = "product-list",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="filter_data", type="object",
     *                      @OA\Property(property="name", type="object",
     *                               @OA\Property(property="filterType", type="string"),
     *                               @OA\Property(property="type", type="string"),
     *                               @OA\Property(property="filter", type="string")
     *                      ),
     *                @OA\Property(property="status", type="object",
     *                               @OA\Property(property="filterType", type="string"),
     *                               @OA\Property(property="type", type="string"),
     *                               @OA\Property(property="filter", type="string")
     *                      ),
     *                 @OA\Property(property="created_at", type="object",
     *                               @OA\Property(property="filterType", type="string"),
     *                               @OA\Property(property="type", type="string"),
     *                               @OA\Property(property="dateFrom", type="string"),
     *                               @OA\Property(property="dateTo", type="string"),
     *                      ),
     *                 ),
     *               @OA\Property(property="sort_data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="colId", type="string"),
     *                      @OA\Property(property="sort", type="string")
     *                  )
     *              ),
     *              @OA\Property(property="per_page", type="integer"),
     *              @OA\Property(property="page", type="integer"),
     *            )
     *        )
     *   ),
     *      @OA\Response(
     *          response = 200,
     *          description="Success",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      ),
     * )
     */
    public function index(Request $request)
    {
        try{
            $postData   = $request->all();
            $pageNumber = !empty($postData['page']) ? $postData['page'] : 1;
            $pageLimit  = !empty($postData['per_page']) ? $postData['per_page'] : 50;
            $skip       = ($pageNumber - 1) * $pageLimit;

            $listData = $this->productService->list($postData, $skip, $pageLimit);
            $count    = 0;
            $rows     = [];
            if (!empty($listData) && isset($listData['count']) && isset($listData['data'])) {
                $rows = $listData['data'];
                $count = (int) $listData['count'];
            }

            return General::setResponse("SUCCESS", [], compact('count', 'rows'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *    path="/products/create",
     *    tags={"Products"},
     *    summary = "Create new Product",
     *    security={{"bearer_token":{}}, {"x_localization":{}}},
     *    @OA\RequestBody(
     *        required=true,
     *        @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *                type="object",
     *                required={"product_type_id","name"},
     *                @OA\Property(property="product_type_id", type="integer"),
     *                @OA\Property(property="name", type="string"),
     *                @OA\Property(property="short_description", type="string"),
     *                @OA\Property(property="long_description", type="string"),
     *                @OA\Property(property="product_url", type="string"),
     *                @OA\Property(property="video_url", type="string"),
     *                @OA\Property(property="seo_text", type="string"),
     *                @OA\Property(property="extra_link1", type="string"),
     *                @OA\Property(property="extra_link2", type="string"),
     *                @OA\Property(property="extra_link3", type="string"),
     *                @OA\Property(property="use_case1", type="string"),
     *                @OA\Property(property="use_case2", type="string"),
     *                @OA\Property(property="use_case3", type="string"),
     *                @OA\Property(property="additional_info", type="string"),
     *                @OA\Property(property="twitter", type="string"),
     *                @OA\Property(property="facebook", type="string"),
     *                @OA\Property(property="linkedin", type="string"),
     *                @OA\Property(property="telegram", type="string"),
     *                @OA\Property(property="published_at", type="string", format="date-time"),
     *                @OA\Property(property="payment_status", type="string", enum={"Pending","Success","Failed","ReadyForRefund"}),
     *                @OA\Property(property="status", type="string", enum={"Pending","OneTimeLinkPending","OneTimeLinkUsed"}),
     *                @OA\Property(property="is_verified", type="boolean"),
     *                @OA\Property(property="is_gold", type="boolean"),
     *                @OA\Property(property="is_human_verified", type="boolean"),
     *                @OA\Property(property="one_time_token", type="string"),
     *                @OA\Property(property="is_token_used", type="boolean")
     *            )
     *        )
     *    ),
     *    @OA\Response(
     *        response=200,
     *        description="Product created successfully",
     *        @OA\MediaType(
     *            mediaType="application/json",
     *        )
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Unauthorized"
     *    ),
     *    @OA\Response(
     *        response=400,
     *        description="Invalid request"
     *    ),
     *    @OA\Response(
     *        response=404,
     *        description="not found"
     *    )
     * )
     */
    public function store(StoreProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->productService->store($request);

            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_saved_successfully', ['moduleName' => __('labels.product')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/products/{id}/details",
     *   tags={"Products"},
     *   summary="Get Product details",
     *  security={{"bearer_token":{}}, {"x_localization":{}}},
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *
     *
     *
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden"
     *   ),
     *   @OA\Response(
     *      response=500,
     *      description="Server Error"
     *   )
     * )
     */
    public function show($id)
    {
        try {
            $data = $this->productService->detailsByID($id);
            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.product')]));
            }
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/products/{id}/update",
     * tags = {"Products"},
     * summary = "Update Product",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\RequestBody(
     *        required=true,
     *        @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *                type="object",
     *                required={"product_type_id","name"},
     *                @OA\Property(property="product_type_id", type="integer"),
     *                @OA\Property(property="name", type="string"),
     *                @OA\Property(property="short_description", type="string"),
     *                @OA\Property(property="long_description", type="string"),
     *                @OA\Property(property="product_url", type="string"),
     *                @OA\Property(property="video_url", type="string"),
     *                @OA\Property(property="seo_text", type="string"),
     *                @OA\Property(property="extra_link1", type="string"),
     *                @OA\Property(property="extra_link2", type="string"),
     *                @OA\Property(property="extra_link3", type="string"),
     *                @OA\Property(property="use_case1", type="string"),
     *                @OA\Property(property="use_case2", type="string"),
     *                @OA\Property(property="use_case3", type="string"),
     *                @OA\Property(property="additional_info", type="string"),
     *                @OA\Property(property="twitter", type="string"),
     *                @OA\Property(property="facebook", type="string"),
     *                @OA\Property(property="linkedin", type="string"),
     *                @OA\Property(property="telegram", type="string"),
     *                @OA\Property(property="published_at", type="string", format="date-time"),
     *                @OA\Property(property="payment_status", type="string", enum={"Pending","Success","Failed","ReadyForRefund"}),
     *                @OA\Property(property="status", type="string", enum={"Pending","OneTimeLinkPending","OneTimeLinkUsed"}),
     *                @OA\Property(property="is_verified", type="boolean"),
     *                @OA\Property(property="is_gold", type="boolean"),
     *                @OA\Property(property="is_human_verified", type="boolean"),
     *                @OA\Property(property="one_time_token", type="string"),
     *                @OA\Property(property="is_token_used", type="boolean")
     *            )
     *        )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden"
     *   ),
     *   @OA\Response(
     *      response=500,
     *      description="Server Error"
     *   )
     * )
     */
    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $data = $this->productService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.product')]));
            }

            DB::beginTransaction();
            $data = $this->productService->update($id, $request);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_updated_successfully', ['moduleName' => __('labels.product')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     ** path="/products/{id}/delete",
     *   tags={"Products"},
     *   summary="Delete Product",
     *  security={{"bearer_token":{}}, {"x_localization":{}}},
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden"
     *   ),
     *   @OA\Response(
     *      response=500,
     *      description="Server Error"
     *   )
     * )
     */
    public function destroy($id)
    {
        try {
            $data = $this->productService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.product')]));
            }
            DB::beginTransaction();
            $data = $this->productService->destroy($id);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_deleted_successfully', ['moduleName' => __('labels.product')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/products/{id}/change-status",
     * tags = {"Products"},
     * summary = "Change Product status",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="status",
     *      in="query",
     *      required=true,
     *      description="Validations: Pending, OneTimeLinkPending, OneTimeLinkUsed",
     *      @OA\Schema(type="string", enum={"Pending","OneTimeLinkPending","OneTimeLinkUsed"})
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden"
     *   ),
     *   @OA\Response(
     *      response=500,
     *      description="Server Error"
     *   )
     * )
     */
    public function changeStatus(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $data = $this->productService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.product')]));
            }

            $data = $this->productService->changeStatus($id, $request);
            $status = $data->status == 'Active' ? 'activated' : 'deactivated';
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_status_changed_successfully', ['module' => __('labels.product'), 'moduleName' => $status]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/products/active/list",
     *   tags={"Products"},
     *   summary="Get active products",
     *  security={{"bearer_token":{}}, {"x_localization":{}}},
     *   @OA\Parameter(
     *      name="product_type_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden"
     *   ),
     *   @OA\Response(
     *      response=500,
     *      description="Server Error"
     *   )
     * )
     */
    public function getActiveProducts(Request $request)
    {
        try {
            $productTypeId = $request->get('product_type_id');
            $data = $this->productService->getAllActiveProducts($productTypeId);
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }
}
