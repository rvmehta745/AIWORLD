<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Requests\V1\Category\StoreCategoryRequest;
use App\Http\Requests\V1\Category\UpdateCategoryRequest;
use App\Services\V1\CategoryService;
use Illuminate\Http\Request;
use App\Library\General;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API endpoints for managing Categories"
 * )
 */
class CategoryController extends \App\Http\Controllers\V1\BaseController
{
    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @OA\Post(
     * path="/categories",
     * tags = {"Categories"},
     * summary = "Get list of Categories",
     * operationId = "category-list",
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

            $listData = $this->categoryService->list($postData, $skip, $pageLimit);
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
     *    path="/categories/create",
     *    tags={"Categories"},
     *    summary = "Create new Category",
     *    security={{"bearer_token":{}}, {"x_localization":{}}},
     *    @OA\Parameter(
     *        name="product_type_id",
     *        in="query",
     *        required=true,
     *        description="Product Type ID",
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(
     *        name="parent_id",
     *        in="query",
     *        required=false,
     *        description="Parent Category ID",
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(
     *        name="name",
     *        in="query",
     *        required=true,
     *        description="Category Name - Validations: required, unique, max:255",
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(
     *        name="description",
     *        in="query",
     *        required=false,
     *        description="Category Description",
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(
     *        name="tools_count",
     *        in="query",
     *        required=false,
     *        description="Tools Count",
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(
     *        name="sort_order",
     *        in="query",
     *        required=false,
     *        description="Sort Order",
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(
     *        name="status",
     *        in="query",
     *        required=false,
     *        description="Status - Valid values: Active, InActive",
     *        @OA\Schema(type="string", enum={"Active", "InActive"})
     *    ),
     *    @OA\RequestBody(
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *                @OA\Property(
     *                    property="logo",
     *                    type="string",
     *                    format="binary",
     *                    description="Category Logo Image"
     *                )
     *            )
     *        )
     *    ),
     *  @OA\Response(
     *        response=200,
     *        description="Category created successfully",
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
    public function store(StoreCategoryRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->categoryService->store($request);

            DB::commit();
            return General::setResponse("SUCCESS",'Category created successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/categories/{id}/details",
     *   tags={"Categories"},
     *   summary="Get Category details",
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
    public function show($id)
    {
        try {
            $data = $this->categoryService->detailsByID($id);
            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.category')]));
            }
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/categories/{id}/update",
     * tags = {"Categories"},
     * summary = "Update Category",
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
     *        name="product_type_id",
     *        in="query",
     *        required=true,
     *        description="Product Type ID",
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(
     *        name="parent_id",
     *        in="query",
     *        required=false,
     *        description="Parent Category ID",
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(
     *        name="name",
     *        in="query",
     *        required=true,
     *        description="Category Name - Validations: required, max:255",
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(
     *        name="description",
     *        in="query",
     *        required=false,
     *        description="Category Description",
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(
     *        name="tools_count",
     *        in="query",
     *        required=false,
     *        description="Tools Count",
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(
     *        name="sort_order",
     *        in="query",
     *        required=false,
     *        description="Sort Order",
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(
     *        name="status",
     *        in="query",
     *        required=false,
     *        description="Status - Valid values: Active, InActive",
     *        @OA\Schema(type="string", enum={"Active", "InActive"})
     *    ),
     *    @OA\RequestBody(
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *                @OA\Property(
     *                    property="logo",
     *                    type="string",
     *                    format="binary",
     *                    description="Category Logo Image"
     *                )
     *            )
     *        )
     *    ),
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
    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $data = $this->categoryService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.category')]));
            }

            DB::beginTransaction();
            $data = $this->categoryService->update($id, $request);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_updated_successfully', ['moduleName' => __('labels.category')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     ** path="/categories/{id}/delete",
     *   tags={"Categories"},
     *   summary="Delete Category",
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
            $data = $this->categoryService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.category')]));
            }

            $this->categoryService->destroy($id);
            return General::setResponse("SUCCESS", __('messages.module_name_deleted_successfully', ['moduleName' => __('labels.category')]));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     ** path="/categories/{id}/change-status",
     *   tags={"Categories"},
     *   summary="Change status of Category",
     *  security={{"bearer_token":{}}, {"x_localization":{}}},
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *               required={"status"},
     *               @OA\Property(property="status", type="string", description="Valid values: Active, InActive")
     *             )
     *         )
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
            $status = $request->status;
            if (!in_array($status, ['Active', 'InActive'])) {
                return General::setResponse("OTHER_ERROR", __('messages.failed_to_change_status', ['moduleName' => __('labels.category')]));
            }

            $data = $this->categoryService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.category')]));
            }

            DB::beginTransaction();
            $this->categoryService->changeStatus($id, $request);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_status_changed_successfully', ['moduleName' => __('labels.category')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     * path="/categories/active/list",
     * tags={"Categories"},
     * summary="Get active categories",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
     * @OA\Parameter(
     * name="product_type_id",
     * in="query",
     * required=false,
     * @OA\Schema(
     * type="integer"
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Success",
     * @OA\MediaType(
     * mediaType="application/json"
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * ),
     * @OA\Response(
     * response=400,
     * description="Bad Request"
     * ),
     * @OA\Response(
     * response=404,
     * description="not found"
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden"
     * ),
     * @OA\Response(
     * response=500,
     * description="Server Error"
     * )
     * )
     */
    public function getActiveCategories(Request $request)
    {
        try {
            $productTypeId = $request->get('product_type_id');
            $data = $this->categoryService->getAllActiveCategories($productTypeId);
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }
} 