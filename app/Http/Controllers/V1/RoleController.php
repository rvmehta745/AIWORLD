<?php

namespace App\Http\Controllers\V1;

use App\Http\Requests\V1\Role\StoreRoleRequest;
use App\Http\Requests\V1\Role\UpdateRoleRequest;
use App\Services\V1\RoleService;
use Illuminate\Http\Request;
use App\Library\General;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @OA\Tag(
 *     name="Role Management",
 *     description="API endpoints for managing Roles"
 * )
 */
class RoleController extends BaseController
{
    private RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * @OA\Post(
     *    path="/roles/create",
     *    tags={"Role Management"},
     *    summary = "Create new Role",
     *    security={{"bearer_token":{}}, {"x_localization":{}}},
     *   @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              required={"name"},
     *             @OA\Property(
     *                property="name",
     *                type="string",
     *                description="Role name - Validations: min=3, max=50, unique",
     *             ),
     *             @OA\Property(
     *                property="privileges",
     *                type="array",
     *                description="Array of privilege IDs",
     *                @OA\Items(type="integer")
     *             ),
     *             @OA\Property(
     *                property="is_editable",
     *                type="boolean",
     *                description="Whether role is editable (default: true)",
     *             ),
     *             @OA\Property(
     *                property="is_active",
     *                type="boolean",
     *                description="Whether role is active (default: true)",
     *             ),
     *         ),
     *      ),
     *   ),
     *  @OA\Response(
     *        response=200,
     *        description="Role created successfully",
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
     *    ),
     * )
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            DB::beginTransaction();
            $role = $this->roleService->store($request);
            DB::commit();
            return General::setResponse("SUCCESS", 'Role created successfully', compact('role'));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/roles/{id}/details",
     *   tags={"Role Management"},
     *   summary="Get Role details by ID",
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
    public function show($identifier)
    {
        try {
            if (is_numeric($identifier)) {
                $data = $this->roleService->details($identifier); // fetch by ID
            } else {
                $data = $this->roleService->detailsBySlug($identifier); // fetch by Slug
            }

            if (empty($data)) {
                return General::setResponse(
                    "OTHER_ERROR",
                    __('messages.module_name_not_found', ['moduleName' => __('labels.roles')])
                );
            }

            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/roles/{id}/update",
     * tags = {"Role Management"},
     * summary = "Update Role",
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
     *             mediaType="application/json",
     *             @OA\Schema(
     *              required={"name"},
     *             @OA\Property(
     *                property="name",
     *                type="string",
     *                description="Role name - Validations: min=3, max=50, unique",
     *             ),
     *             @OA\Property(
     *                property="privileges",
     *                type="array",
     *                description="Array of privilege IDs",
     *                @OA\Items(type="integer")
     *             ),
     *             @OA\Property(
     *                property="is_editable",
     *                type="boolean",
     *                description="Whether role is editable",
     *             ),
     *             @OA\Property(
     *                property="is_active",
     *                type="boolean",
     *                description="Whether role is active",
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
    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            $data = $this->roleService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.roles')]));
            }

            DB::beginTransaction();
            $data = $this->roleService->update($id, $request);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_updated_successfully', ['moduleName' => __('labels.roles')]), compact('data'));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     ** path="/roles/{id}/delete",
     *   tags={"Role Management"},
     *   summary="Delete Role",
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
            $data = $this->roleService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.roles')]));
            }

            DB::beginTransaction();
            $data = $this->roleService->destroy($id);
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_deleted_successfully', ['moduleName' => __('labels.roles')]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/roles/{id}/change-status",
     * tags = {"Role Management"},
     * summary = "Change Role status",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
     *
     *      @OA\Parameter(
     *          name = "id",
     *          in = "path",
     *          required = true,
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
            $data = $this->roleService->details($id);

            if (empty($data)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.roles')]));
            }

            $data = $this->roleService->changeStatus($id, $request);
            if ($data->is_active == 1) {
                $is_active = 'activated';
            } else {
                $is_active = 'deactivated';
            }
            DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_status_changed_successfully', ['module' => __('labels.roles'), 'moduleName' => $is_active]));
        } catch (Throwable $e) {
            DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/roles/active/list",
     *   tags={"Role Management"},
     *   summary="Get all active roles for dropdown",
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
    public function getActiveRoles()
    {
        try {
            $data = $this->roleService->getAllActiveRoles();
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }
}
