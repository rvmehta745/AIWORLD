<?php

namespace App\Http\Controllers\V1;

use App\Http\Requests\V1\User\StoreDispositionManagerRequest;
use App\Http\Requests\V1\User\UpdateDispositionManagerRequest;
use App\Services\V1\UserService;
use Illuminate\Http\Request;
use App\Library\General;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @OA\Tag(
 *     name="Admin Users Management",
 *     description="API endpoints for managing Admin Users"
 * )
 */
class UserController extends BaseController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Post(
     * path="/admin-users",
     * tags = {"Admin Users Management"},
     * summary = "Get list of Admin Users",
     * operationId = "user-list",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *               type="object",
     *               @OA\Property(property="filter_data", type="object",
     *                      @OA\Property(property="first_name", type="object",
     *                               @OA\Property(property="filterType", type="string"),
     *                               @OA\Property(property="type", type="string"),
     *                               @OA\Property(property="filter", type="string")
     *                      ),
     *                      @OA\Property(property="last_name", type="object",
     *                               @OA\Property(property="filterType", type="string"),
     *                               @OA\Property(property="type", type="string"),
     *                               @OA\Property(property="filter", type="string")
     *                      ),
     *                @OA\Property(property="email", type="object",
     *                               @OA\Property(property="filterType", type="string"),
     *                               @OA\Property(property="type", type="string"),
     *                               @OA\Property(property="filter", type="string")
     *                      ),
     *                @OA\Property(property="phone_number", type="object",
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
     *                 @OA\Property(property="is_active", type="object",
     *                               @OA\Property(property="filterType", type="string"),
     *                               @OA\Property(property="type", type="string"),
     *                               @OA\Property(property="filter", type="string")
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

            $listData = $this->userService->list($postData, $skip, $pageLimit);
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
     *    path="/admin-users/create",
     *    tags={"Admin Users Management"},
     *    summary = "Create new User",
     *    security={{"bearer_token":{}}, {"x_localization":{}}},
     *   @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *              required={"first_name","last_name","password","email","role"},
     *             @OA\Property(
     *                property="first_name",
     *                type="string",
     *                description="Validations: min=3, max=50",
     *             ),
     *             @OA\Property(
     *                property="last_name",
     *                type="string",
     *                description="Validations: min=3, max=50",
     *             ),
     *           @OA\Property(
     *                property="email",
     *                type="string",
     *                description="Validations: min=3, max=70",
     *             ),
     *           @OA\Property(
     *                property="password",
     *                type="string",
     *                description="Validations: min=8, max=20",
     *             ),
     *           @OA\Property(
     *                property="role",
     *                type="string",
     *                description="User role - Valid values: Super Admin, Admin, Users",
     *             ),
     *           @OA\Property(
     *                property="phone_number",
     *                type="string",
     *                description="Phone number (optional)",
     *             ),
     *           @OA\Property(
     *                property="country_code",
     *                type="string",
     *                description="Country code (optional)",
     *             ),
     *         ),
     *      ),
     *   ),
     *  @OA\Response(
     *        response=200,
     *        description="User created successfully",
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
    public function store(StoreDispositionManagerRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->userService->store($request);

            DB::commit();
            return General::setResponse("SUCCESS",'User created successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/admin-users/{id}/details",
     *   tags={"Admin Users Management"},
     *   summary="Get Admin User details",
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
            $data = $this->userService->detailsByID($id);
            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.user')]));
            }
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/admin-users/{id}/update",
     * tags = {"Admin Users Management"},
     * summary = "Update User",
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
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *              required={"first_name","last_name","email","role"},
     *             @OA\Property(
     *                property="first_name",
     *                type="string",
     *                description="Validations: min=3, max=50",
     *             ),
     *             @OA\Property(
     *                property="last_name",
     *                type="string",
     *                description="Validations: min=3, max=50",
     *             ),
     *           @OA\Property(
     *                property="email",
     *                type="string",
     *                description="Validations: min=3, max=70",
     *             ),
     *           @OA\Property(
     *                property="role",
     *                type="string",
     *                description="User role - Valid values: Super Admin, Admin, Users",
     *             ),
     *           @OA\Property(
     *                property="phone_number",
     *                type="string",
     *                description="Phone number (optional)",
     *             ),
     *           @OA\Property(
     *                property="country_code",
     *                type="string",
     *                description="Country code (optional)",
     *             ),
     *         ),
     *      ),
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
    public function update(UpdateDispositionManagerRequest $request, $id)
    {
        try {
            $data = $this->userService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.user')]));
            }

            DB::beginTransaction();
            $data = $this->userService->update($id, $request);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_updated_successfully', ['moduleName' => __('labels.user')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     ** path="/admin-users/{id}/delete",
     *   tags={"Admin Users Management"},
     *   summary="Delete Admin User",
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
            $data = $this->userService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.user')]));
            }
            DB::beginTransaction();
            $data = $this->userService->destory($id);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_deleted_successfully', ['moduleName' => __('labels.user')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/admin-users/{id}/change-status",
     * tags = {"Admin Users Management"},
     * summary = "Change Admin User status",
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
     *          name = "is_active",
     *          in = "query",
     *          required = true,
     *          description="Validations: 0,1",
     *          @OA\Schema(
     *              type ="integer"
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
            $data = $this->userService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.user')]));
            }

            $data = $this->userService->changeStatus($id, $request);
            if ($data->is_active == 1) {
                $is_active = 'activated';
            } else {
                $is_active = 'deactivated';
            }
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_status_changed_successfully', ['module' => __('labels.user'), 'moduleName' => $is_active]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/admin-users/active/list",
     *   tags={"Admin Users Management"},
     *   summary="Get all active admin users for dropdown",
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
    public function getActiveUsers()
    {
        try {
            $data = $this->userService->getAllActiveUsers();
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }
}