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
        try {
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

            return General::setResponse("SUCCESS", ["Product Type Fetch Successfully"], compact('count', 'rows'));
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
     *    @OA\RequestBody(
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *                @OA\Property(
     *                    property="logo",
     *                    type="string",
     *                    format="binary",
     *                    description="Product Type Logo Image"
     *                )
     *            )
     *        )
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
            return General::setResponse("CREATED", 'Product Type created successfully');
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
            return General::setResponse("SUCCESS", ['Product Type Details Fetched Successfully'], compact('data'));
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
     *    @OA\RequestBody(
     *        required=true,
     *        @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *                type="object",
     *                required={"name"},
     *                @OA\Property(
     *                    property="name",
     *                    type="string",
     *                    maxLength=255,
     *                    description="Product Type Name",
     *                    example="AI Tools"
     *                ),
     *                @OA\Property(
     *                    property="tag_line",
     *                    type="string",
     *                    nullable=true,
     *                    maxLength=255,
     *                    description="Tag Line",
     *                    example="Advanced AI Solutions"
     *                ),
     *                @OA\Property(
     *                    property="configuration",
     *                    type="string",
     *                    nullable=true,
     *                    description="Configuration JSON",
     *                    example="{\"key\": \"value\"}"
     *                ),
     *                @OA\Property(
     *                    property="sort_order",
     *                    type="integer",
     *                    nullable=true,
     *                    description="Sort Order",
     *                    example=1
     *                ),
     *                @OA\Property(
     *                    property="status",
     *                    type="string",
     *                    nullable=true,
     *                    enum={"Active", "InActive"},
     *                    description="Status",
     *                    example="Active"
     *                ),
     *                @OA\Property(
     *                    property="logo",
     *                    type="string",
     *                    nullable=true,
     *                    description="Product Type Logo Image as base64 string (data:image/png;base64,...) or can be omitted",
     *                    example="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=="
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

    /**
     * @OA\Post(
     * path="/product-types/reorder",
     * tags = {"Product Types"},
     * summary = "Reorder product types based on drag and drop",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             required={"product_type_id", "old_position", "new_position"},
     *             @OA\Property(property="product_type_id", type="integer", description="ID of the product type to reorder"),
     *             @OA\Property(property="old_position", type="integer", minimum=1, description="Current position of the product type"),
     *             @OA\Property(property="new_position", type="integer", minimum=1, description="New position for the product type")
     *         )
     *     )
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Product type reordered successfully",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     )
     * ),
     * @OA\Response(
     *     response=400,
     *     description="Bad Request - No change needed",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="no_change")
     *         )
     *     )
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden"),
     * @OA\Response(response=404, description="Product type not found"),
     * @OA\Response(response=500, description="Server Error")
     * )
     */
    public function reorder(Request $request)
    {
        try {
            $request->validate([
                'product_type_id' => 'required|integer|exists:product_types,id',
                'old_position'    => 'required|integer|min:1',
                'new_position'    => 'required|integer|min:1',
            ]);

            $productTypeId = $request->product_type_id;
            $oldPosition   = $request->old_position;
            $newPosition   = $request->new_position;

            if ($oldPosition == $newPosition) {
                return response()->json(['status' => 'no_change']);
            }

            DB::beginTransaction();

            // Get product type
            $productType = \App\Models\ProductType::where('id', $productTypeId)->firstOrFail();

            if ($oldPosition < $newPosition) {
                // Shift UP (dragging downwards)
                // Move product types between old_position+1 and new_position down by 1
                \App\Models\ProductType::whereBetween('sort_order', [$oldPosition + 1, $newPosition])
                    ->decrement('sort_order');
            } else {
                // Shift DOWN (dragging upwards)
                // Move product types between new_position and old_position-1 up by 1
                \App\Models\ProductType::whereBetween('sort_order', [$newPosition, $oldPosition - 1])
                    ->increment('sort_order');
            }

            // Set new position for dragged product type
            $productType->sort_order = $newPosition;
            $productType->save();

            DB::commit();
            return response()->json(['status' => 'success']);

        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }
}
