<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Requests\V1\ProductType\StoreProductTypeRequest;
use App\Http\Requests\V1\ProductType\UpdateProductTypeRequest;
use App\Services\V1\ProductTypeService;
use Illuminate\Http\Request;
use App\Library\General;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @OA\Tag(
 *     name="Product Types",
 *     description="API endpoints for managing Product Types"
 * )
 */
class ProductTypeController extends \App\Http\Controllers\V1\BaseController
{
    private ProductTypeService $productTypeService;

    public function __construct(ProductTypeService $productTypeService)
    {
        $this->productTypeService = $productTypeService;
    }

    /**
     * @OA\Post(
     * path="/product-types",
     * tags = {"Product Types"},
     * summary = "Get list of Product Types",
     * operationId = "product-type-list",
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
     *                      @OA\Property(property="tag_line", type="object",
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

            $listData = $this->productTypeService->list($postData, $skip, $pageLimit);
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
     *    path="/product-types/create",
     *    tags={"Product Types"},
     *    summary = "Create new Product Type",
     *    security={{"bearer_token":{}}, {"x_localization":{}}},
     *    @OA\Parameter(
     *        name="name",
     *        in="query",
     *        required=true,
     *        description="Product Type Name - Validations: required, unique, max:255",
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(
     *        name="tag_line",
     *        in="query",
     *        required=false,
     *        description="Tag Line - Validations: nullable, max:255",
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(
     *        name="configuration",
     *        in="query",
     *        required=false,
     *        description="Configuration JSON - Validations: nullable",
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(
     *        name="sort_order",
     *        in="query",
     *        required=false,
     *        description="Sort Order - Validations: nullable, integer",
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(
     *        name="status",
     *        in="query",
     *        required=false,
     *        description="Status - Valid values: Active, InActive",
     *        @OA\Schema(type="string", enum={"Active", "InActive"})
     *    ),
     *  @OA\Response(
     *        response=200,
     *        description="Product Type created successfully",
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
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
     *    ),
     * )
     */
    public function store(StoreProductTypeRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->productTypeService->store($request);

            DB::commit();
            return General::setResponse("SUCCESS",'Product Type created successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/product-types/{id}/details",
     *   tags={"Product Types"},
     *   summary="Get Product Type details",
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
     *)
     **/
    public function show($id)
    {
        try {
            $data = $this->productTypeService->detailsByID($id);
            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.product_type')]));
            }
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/product-types/{id}/update",
     * tags = {"Product Types"},
     * summary = "Update Product Type",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
     *
     *      @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *     ),
     *    @OA\Parameter(
     *        name="name",
     *        in="query",
     *        required=true,
     *        description="Product Type Name - Validations: required, max:255",
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(
     *        name="tag_line",
     *        in="query",
     *        required=false,
     *        description="Tag Line - Validations: nullable, max:255",
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(
     *        name="configuration",
     *        in="query",
     *        required=false,
     *        description="Configuration JSON - Validations: nullable",
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(
     *        name="sort_order",
     *        in="query",
     *        required=false,
     *        description="Sort Order - Validations: nullable, integer",
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(
     *        name="status",
     *        in="query",
     *        required=false,
     *        description="Status - Valid values: Active, InActive",
     *        @OA\Schema(type="string", enum={"Active", "InActive"})
     *    ),
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
    public function update(UpdateProductTypeRequest $request, $id)
    {
        try {
            $data = $this->productTypeService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.product_type')]));
            }

            DB::beginTransaction();
            $data = $this->productTypeService->update($id, $request);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_updated_successfully', ['moduleName' => __('labels.product_type')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     ** path="/product-types/{id}/delete",
     *   tags={"Product Types"},
     *   summary="Delete Product Type",
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
     *)
     **/
    public function destroy($id)
    {
        try {
            $data = $this->productTypeService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.product_type')]));
            }
            DB::beginTransaction();
            $data = $this->productTypeService->destroy($id);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_deleted_successfully', ['moduleName' => __('labels.product_type')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/product-types/{id}/change-status",
     * tags = {"Product Types"},
     * summary = "Change Product Type status",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
     *
     *      @OA\Parameter(
     *          name = "id",
     *          in = "path",
     *          required = false,
     *          @OA\Schema(
     *              type ="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name = "status",
     *          in = "query",
     *          required = true,
     *          description="Validations: Active, InActive",
     *          @OA\Schema(
     *              type ="string"
     *          )
     *      ),
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
    public function changeStatus(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $data = $this->productTypeService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.product_type')]));
            }

            $data = $this->productTypeService->changeStatus($id, $request);
            $status = $data->status == 'Active' ? 'activated' : 'deactivated';
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_status_changed_successfully', ['module' => __('labels.product_type'), 'moduleName' => $status]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/product-types/active/list",
     *   tags={"Product Types"},
     *   summary="Get all active product types for dropdown",
     *  security={{"bearer_token":{}}, {"x_localization":{}}},
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
     *)
     **/
    public function getActiveProductTypes()
    {
        try {
            $data = $this->productTypeService->getAllActiveProductTypes();
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }
}
