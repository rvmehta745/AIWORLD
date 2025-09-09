<?php

namespace App\Repositories\V1;

use App\Models\Role;
use App\Models\LovPrivileges;
use App\Repositories\BaseRepository;
use App\Traits\CommonTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RoleRepository extends BaseRepository
{
    use CommonTrait;

    private Role $role;

    public function __construct()
    {
        $this->role = new Role();
    }

    /**
     * List Roles
     */
    public function list($postData, $page, $perPage)
    {
        $query = DB::table('mst_roles')
            ->whereNull('mst_roles.deleted_at');

        if (!empty($postData['filter_data'])) {
            foreach ($postData['filter_data'] as $key => $value) {
                if (in_array($key, ["name", "is_editable", "is_active"])) {
                    switch ($key) {
                        case "is_editable":
                        case "is_active":
                            $key = 'mst_roles.' . $key;
                            $query = $this->createWhere('set', $key, $value, $query);
                            break;
                        default:
                            $key = 'mst_roles.' . $key;
                            $query = $this->createWhere('text', $key, $value, $query);
                            break;
                    }
                }

                if (in_array($key, ["created_at", "updated_at"])) {
                    $key   = 'mst_roles.' . $key;
                    $query = $this->createWhere('date', $key, $value, $query);
                }
            }
        }

        $query = $query->select(
            'mst_roles.id',
            'mst_roles.name',
            'mst_roles.slug',
            'mst_roles.privileges',
            'mst_roles.is_editable',
            'mst_roles.is_active',
            'mst_roles.created_at',
            'mst_roles.updated_at'
        );

        $orderBy   = 'mst_roles.updated_at';
        $orderType = (isset($postData['order_by']) && $postData['order_by'] == 1) ? 'asc' : 'desc';
        if (!empty($postData['sort_data'])) {
            $orderBy   = $postData['sort_data'][0]['colId'];
            $orderType = $postData['sort_data'][0]['sort'];
        }
        $query       = $query->orderBy($orderBy, $orderType);
        $count       = $query->count();
        $query = Role::query()->whereNull('deleted_at');

        $dataPerPage = $query->skip($page)->take($perPage)->get();

        foreach ($dataPerPage as $role) {
            if ($role->privileges) {
                $role->privileges_list = $this->getPrivilegesDetails($role->privileges);
            }
        }

        return ['data' => $dataPerPage, 'count' => $count];
    }

    /**
     * Store Role
     */
    public function store($request)
    {
        $storeData = [
            'name' => $request->name,
            'privileges' => $this->formatPrivileges($request->privileges),
            'is_editable' => $request->is_editable ?? 1,
            'is_active' => $request->is_active ?? 1,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ];

        return Role::create($storeData);
    }

    /**
     * Get Role Details by ID
     */
    public function details($id)
    {
        $role = $this->role
            ->select('id', 'name', 'slug', 'privileges', 'is_editable', 'is_active', 'created_at', 'updated_at')
            ->where('id', $id)
            ->first();

        if (empty($role)) {
            return null;
        }

        // Parse privileges and get privilege details
        $role->privileges_list = $this->getPrivilegesDetails($role->privileges);

        return $role;
    }

    /**
     * Get Role Details by Slug
     */
    public function detailsBySlug($slug)
    {
        $role = $this->role
            ->select('id', 'name', 'slug', 'privileges', 'is_editable', 'is_active', 'created_at', 'updated_at')
            ->where('slug', $slug)
            ->first();

        if (empty($role)) {
            return null;
        }

        // Parse privileges and get privilege details
        $role->privileges_list = $this->getPrivilegesDetails($role->privileges);

        return $role;
    }

    /**
     * Update Role
     */
    public function update($id, $request)
    {
        $role = $this->role->find($id);

        $updateData = [
            'name' => $request->name,
            'privileges' => $this->formatPrivileges($request->privileges),
            'is_editable' => $request->is_editable ?? $role->is_editable,
            'is_active' => $request->is_active ?? $role->is_active,
            'updated_by' => Auth::user()->id,
        ];

        $role->update($updateData);
        return $role;
    }

    /**
     * Delete Role
     */
    public function destroy($id)
    {
        $role = $this->role->find($id);

        // Check if role is editable
        if ($role->is_editable == 0) {
            throw new \Exception('This role cannot be deleted as it is not editable.');
        }

        return $role->delete();
    }

    /**
     * Change Role Status
     */
    public function changeStatus($id, $request)
    {
        $role = $this->role->find($id);

        // Check if role is editable
        if ($role->is_editable == 0) {
            throw new \Exception('This role status cannot be changed as it is not editable.');
        }

        $role->update([
            'is_active' => $request->is_active == 1 ? 1 : 0,
            'updated_by' => Auth::user()->id,
        ]);

        return $role;
    }

    /**
     * Format privileges array to string format
     */
    private function formatPrivileges($privileges)
    {
        if (is_array($privileges)) {
            // Convert array to string format: #10001#10002#10003#
            return '#' . implode('#', $privileges) . '#';
        }

        if (is_string($privileges)) {
            // If already in string format, ensure it starts and ends with #
            $privileges = trim($privileges, '#');
            return '#' . $privileges . '#';
        }

        return null;
    }

    /**
     * Get privileges details from privilege IDs
     */
    private function getPrivilegesDetails($privilegesString)
    {
        if (empty($privilegesString) || is_null($privilegesString)) {
            return [];
        }

        // Handle different input types
        if (is_array($privilegesString)) {
            // If it's already an array, use it directly
            $privilegeIds = array_filter($privilegesString, function ($value) {
                return !empty($value) && is_numeric($value);
            });
        } elseif (is_string($privilegesString)) {
            // Parse privilege IDs from string format
            $privilegeIds = array_filter(explode('#', $privilegesString), function ($value) {
                return !empty($value) && is_numeric($value);
            });
        } else {
            // For other types, return empty array
            return [];
        }

        if (empty($privilegeIds)) {
            return [];
        }

        // Get privilege details
        $privileges = LovPrivileges::select('id', 'name', 'permission_key', 'path', 'group_id', 'parent_id')
            ->whereIn('id', $privilegeIds)
            ->where('is_active', 1)
            ->orderBy('sequence')
            ->get();

        return $privileges->toArray();
    }

    /**
     * Get all active roles for dropdown
     */
    public function getAllActiveRoles()
    {
        return $this->role
            ->select('id', 'name', 'slug')
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if role name exists (for validation)
     */
    public function checkNameExists($name, $excludeId = null)
    {
        $query = $this->role->where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get total number of roles
     */
    public function getTotalRoleCount()
    {
        return $this->role->whereNull('deleted_at')->count();
    }

    /**
     * Get total number of active roles
     */
    public function getActiveRoleCount()
    {
        return $this->role->whereNull('deleted_at')->where('is_active', 1)->count();
    }
}
