<?php

namespace App\Repositories\V1;

use App\Models\Category;
use App\Models\LovPrivileges;
use App\Models\Role;
use App\Repositories\BaseRepository;
use App\Traits\CommonTrait;
use Illuminate\Support\Facades\DB;

class CommonRepository extends BaseRepository
{
    use CommonTrait;

    public function __construct() {}
    /**
     * Extract IDs from a string formatted as "#1#2#3#"
     *
     * @param string $idString
     * @return array
     */
    private function extractIdsFromString($idString)
    {
        $ids = [];
        $parts = explode('#', $idString);

        foreach ($parts as $part) {
            if (is_numeric($part) && $part !== '') {
                $ids[] = (int)$part;
            }
        }

        return $ids;
    }

    public function roles($postData)
    {
        $query = Role::query()
            ->select('mst_roles.*') // ✅ includes slug
            ->where('mst_roles.is_active', 1)
            ->whereNull('mst_roles.deleted_at');

        if ($postData->role_type == 1) {
            $query->where('mst_roles.role_type', $postData->role_type);
        }
        if ($postData->role_type == 2) {
            $query->where('mst_roles.role_type', $postData->role_type);
        }
        if ($postData->has('search') && !empty($postData->search)) {
            $query->where('mst_roles.name', 'like', '%' . $postData->search . '%');
        }

        return $query->orderBy('mst_roles.id', 'ASC')->get()->toArray();
    }

    public function user($postData, $page = 1, $perPage = 10)
    {
        $query = \DB::table('mst_users')
            ->select('mst_users.id', \DB::raw("CONCAT(mst_users.first_name,' ',mst_users.last_name) as name"), 'mst_users.email', 'mst_users.mobile_no')
            ->where('mst_users.is_active', 1)
            ->whereNull('mst_users.deleted_at');
        if (isset($postData['user_type']) == 1) {
            $query->where('mst_users.user_type', $postData['user_type']);
        }
        if (isset($postData['user_type']) == 2) {
            $query->where('mst_users.user_type', $postData['user_type']);
        }
        if (!empty($postData['search'])) {
            $query->where(function ($where) use ($postData) {
                $where->where(\DB::raw("CONCAT(mst_users.first_name,' ',mst_users.last_name)"), 'like', '%' . $postData['search'] . '%');
                $where->orWhere('mst_users.email', 'like', '%' . $postData['search'] . '%');
            });
        }
        $query       = $query->orderBy('mst_users.first_name');
        $count       = $query->count();
        $dataPerPage = $query->skip($page - 1)->take($perPage)->get()->toArray();

        return ['data' => $dataPerPage, 'count' => $count];
    }

    public function privilegesList($request)
    {
        $query = LovPrivileges::where('parent_id', 0)->with([
            'child' => function ($query) {
                $query->with(['child' => function ($query) {
                    $query->select(['id', 'group_id', 'parent_id', 'path', 'name', 'permission_key', 'is_active']);
                }]);
            }
        ])->select('lov_privileges.id', 'lov_privileges.group_id', 'lov_privileges.parent_id', 'lov_privileges.path', 'lov_privileges.name', 'lov_privileges.permission_key', 'lov_privileges.is_active');
        return $query->get()->toArray();
    }


    /**
     * Get categories by industry for dropdown
     *
     * @param int|null $industryId
     * @return array
     */
    public function categories($industryId = null)
    {
        $query = Category::select('id', 'name', 'industry_id');

        if ($industryId) {
            $query->where('industry_id', $industryId);
        }

        return $query->orderBy('name')
            ->get()
            ->toArray();
    }

    public function getCategories($industryId = null)
    {
        $query = Category::select('id', 'name');
        if (isset($industryId)) {
            // Check if industryId is a string with "#" format
            if (is_string($industryId) && strpos($industryId, '#') !== false) {
                $industryIds = $this->extractIdsFromString($industryId);
                if (!in_array(0, $industryIds)) {
                    $query->whereIn('industry_id', $industryIds);
                }
            }
            // Check if industryId is a direct integer value
            else if (is_numeric($industryId)) {
                $industryId = (int)$industryId;
                // If industryId is 0, don't apply any filter (include all industries)
                if ($industryId !== 0) {
                    $query->where('industry_id', $industryId);
                }
            }
        }
        // if ($industryId) {
        //     $query->where('industry_id', $industryId);
        // }

        return $query->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Get all countries for dropdown
     *
     * @return array
     */

    /**
     * Get states by country for dropdown
     *
     * @param int $countryId
     * @return array
     */

    /**
     * Get cities by state for dropdown
     *
     * @param int $stateId
     * @return array
     */

    /**
     * Get all cities for dropdown (only id and name)
     *
     * @return array
     */

}
