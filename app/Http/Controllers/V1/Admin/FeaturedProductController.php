<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Requests\V1\FeaturedProduct\StoreFeaturedProductRequest;
use App\Http\Requests\V1\FeaturedProduct\UpdateFeaturedProductRequest;
use App\Services\V1\FeaturedProductService;
use Illuminate\Http\Request;
use App\Library\General;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @OA\Tag(
 *     name="Featured Products",
 *     description="API endpoints for managing Featured Products"
 * )
 */
class FeaturedProductController extends \App\Http\Controllers\V1\BaseController
{
    private FeaturedProductService $featuredProductService;

    public function __construct(FeaturedProductService $featuredProductService)
    {
        $this->featuredProductService = $featuredProductService;
    }

    /**
     * @OA\Post(
     * path="/featured-products",
     * tags = {"Featured Products"},
     * summary = "Get list of Featured Products",
     * operationId = "featured-product-list",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="filter_data", type="object",
     *                      @OA\Property(property="product_type", type="object",
     *                               @OA\Property(property="filterType", type="string"),
     *                               @OA\Property(property="type", type="string"),
     *                               @OA\Property(property="filter", type="string")
     *                      ),
     *                @OA\Property(property="featured_url", type="object",
     *                               @OA\Property(property="filterType", type="string"),
     *                               @OA\Property(property="type", type="string"),
     *                               @OA\Property(property="filter", type="string")
     *                      ),
     *                 @OA\Property(property="start_date", type="object",
     *                               @OA\Property(property="filterType", type="string"),
     *                               @OA\Property(property="type", type="string"),
     *                               @OA\Property(property="dateFrom", type="string"),
     *                               @OA\Property(property="dateTo", type="string"),
     *                      ),
     *                 @OA\Property(property="end_date", type="object",
     *                               @OA\Property(property="filterType", type="string"),
     *                               @OA\Property(property="type", type="string"),
     *                               @OA\Property(property="dateFrom", type="string"),
     *                               @OA\Property(property="dateTo", type="string"),
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

            $listData = $this->featuredProductService->list($postData, $skip, $pageLimit);
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
     *    path="/featured-products/create",
     *    tags={"Featured Products"},
     *    summary = "Create new Featured Product",
     *    security={{"bearer_token":{}}, {"x_localization":{}}},
     *    @OA\RequestBody(
     *        required=true,
     *        @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *                type="object",
     *                required={"product_type_id","product_ids","start_date","end_date"},
     *                @OA\Property(property="product_type_id", type="integer", description="Product Type ID"),
     *                @OA\Property(property="product_ids", type="array", 
     *                    @OA\Items(type="integer"),
     *                    description="Array of product IDs (1-10 products)",
     *                    minItems=1,
     *                    maxItems=10
     *                ),
     *                @OA\Property(property="start_date", type="string", format="date", description="Start date (YYYY-MM-DD)"),
     *                @OA\Property(property="end_date", type="string", format="date", description="End date (YYYY-MM-DD)"),
     *                @OA\Property(property="featured_url", type="string", format="url", description="Featured URL (optional)"),
     *                @OA\Property(property="sort_order", type="integer", minimum=0, description="Sort order (optional)")
     *            )
     *        )
     *    ),
     *    @OA\Response(
     *        response=200,
     *        description="Featured Product created successfully",
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
    public function store(StoreFeaturedProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->featuredProductService->store($request);

            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_saved_successfully', ['moduleName' => __('labels.featured_product')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/featured-products/{id}/details",
     *   tags={"Featured Products"},
     *   summary="Get Featured Product details",
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
            $data = $this->featuredProductService->detailsByID($id);
            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.featured_product')]));
            }
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/featured-products/{id}/update",
     * tags = {"Featured Products"},
     * summary = "Update Featured Product",
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
     *                required={"product_type_id","product_ids","start_date","end_date"},
     *                @OA\Property(property="product_type_id", type="integer", description="Product Type ID"),
     *                @OA\Property(property="product_ids", type="array", 
     *                    @OA\Items(type="integer"),
     *                    description="Array of product IDs (1-10 products)",
     *                    minItems=1,
     *                    maxItems=10
     *                ),
     *                @OA\Property(property="start_date", type="string", format="date", description="Start date (YYYY-MM-DD)"),
     *                @OA\Property(property="end_date", type="string", format="date", description="End date (YYYY-MM-DD)"),
     *                @OA\Property(property="featured_url", type="string", format="url", description="Featured URL (optional)"),
     *                @OA\Property(property="sort_order", type="integer", minimum=0, description="Sort order (optional)")
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
    public function update(UpdateFeaturedProductRequest $request, $id)
    {
        try {
            $data = $this->featuredProductService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.featured_product')]));
            }

            DB::beginTransaction();
            $data = $this->featuredProductService->update($id, $request);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_updated_successfully', ['moduleName' => __('labels.featured_product')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     ** path="/featured-products/{id}/delete",
     *   tags={"Featured Products"},
     *   summary="Delete Featured Product",
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
            $data = $this->featuredProductService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.featured_product')]));
            }
            DB::beginTransaction();
            $data = $this->featuredProductService->destroy($id);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_deleted_successfully', ['moduleName' => __('labels.featured_product')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/featured-products/active/list",
     *   tags={"Featured Products"},
     *   summary="Get active featured products",
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
    public function getActiveFeaturedProducts(Request $request)
    {
        try {
            $productTypeId = $request->get('product_type_id');
            $data = $this->featuredProductService->getActiveFeaturedProducts($productTypeId);
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/featured-products/reorder",
     * tags = {"Featured Products"},
     * summary = "Reorder featured products based on drag and drop",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             type="object",
     *             required={"featured_product_id", "old_position", "new_position"},
     *             @OA\Property(property="featured_product_id", type="integer", description="ID of the featured product to reorder"),
     *             @OA\Property(property="old_position", type="integer", minimum=1, description="Current position of the featured product"),
     *             @OA\Property(property="new_position", type="integer", minimum=1, description="New position for the featured product")
     *         )
     *     )
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Featured Product reordered successfully",
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
     * @OA\Response(response=404, description="Featured Product not found"),
     * @OA\Response(response=500, description="Server Error")
     * )
     */
    public function reorder(Request $request)
    {
        try {
            $request->validate([
                'featured_product_id' => 'required|integer|exists:featured_products,id',
                'old_position'  => 'required|integer|min:1',
                'new_position'  => 'required|integer|min:1',
            ]);

            $featuredProductId = $request->featured_product_id;
            $oldPosition = $request->old_position;
            $newPosition = $request->new_position;

            if ($oldPosition == $newPosition) {
                return response()->json(['status' => 'no_change']);
            }

            DB::beginTransaction();

            $result = $this->featuredProductService->reorder($featuredProductId, $oldPosition, $newPosition);

            if ($result) {
                DB::commit();
                return response()->json(['status' => 'success']);
            } else {
                DB::rollBack();
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.featured_product')]));
            }

        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }
} 