<?php

namespace App\Services\V1;

use App\Repositories\V1\RoleRepository;
use App\Services\BaseService;

class RoleService extends BaseService
{
    private RoleRepository $roleRepository;

    public function __construct()
    {
        $this->roleRepository = new RoleRepository;
    }

    /**
     * List Roles
     */
    public function list($postData, $page, $perPage)
    {
        return $this->roleRepository->list($postData, $page, $perPage);
    }

    /**
     * Store Role
     */
    public function store($request)
    {
        // Check if role name already exists
        if ($this->roleRepository->checkNameExists($request->name)) {
            throw new \Exception('Role name already exists.');
        }

        return $this->roleRepository->store($request);
    }

    /**
     * Get Role Details by ID
     */
    public function details($id)
    {
        return $this->roleRepository->details($id);
    }

    /**
     * Get Role Details by Slug
     */
    public function detailsBySlug($slug)
    {
        return $this->roleRepository->detailsBySlug($slug);
    }

    /**
     * Update Role
     */
    public function update($id, $request)
    {
        // Check if role exists
        $role = $this->roleRepository->details($id);
        if (!$role) {
            throw new \Exception('Role not found.');
        }

        // Check if role is editable
        if ($role->is_editable == 0) {
            throw new \Exception('This role cannot be updated as it is not editable.');
        }

        // Check if role name already exists (excluding current role)
        if ($this->roleRepository->checkNameExists($request->name, $id)) {
            throw new \Exception('Role name already exists.');
        }

        return $this->roleRepository->update($id, $request);
    }

    /**
     * Delete Role
     */
    public function destroy($id)
    {
        // Check if role exists
        $role = $this->roleRepository->details($id);
        if (!$role) {
            throw new \Exception('Role not found.');
        }

        return $this->roleRepository->destroy($id);
    }

    /**
     * Change Role Status
     */
    public function changeStatus($id, $request)
    {
        // Check if role exists
        $role = $this->roleRepository->details($id);
        if (!$role) {
            throw new \Exception('Role not found.');
        }

        return $this->roleRepository->changeStatus($id, $request);
    }

    /**
     * Get all active roles for dropdown
     */
    public function getAllActiveRoles()
    {
        return $this->roleRepository->getAllActiveRoles();
    }

    /**
     * Get total number of roles
     */
    public function getTotalRoleCount()
    {
        return $this->roleRepository->getTotalRoleCount();
    }

    /**
     * Get total number of active roles
     */
    public function getActiveRoleCount()
    {
        return $this->roleRepository->getActiveRoleCount();
    }

    /**
     * Validate privileges format
     */
    public function validatePrivileges($privileges)
    {
        if (empty($privileges)) {
            return true;
        }

        if (is_array($privileges)) {
            // Check if all privilege IDs are numeric
            foreach ($privileges as $privilegeId) {
                if (!is_numeric($privilegeId)) {
                    return false;
                }
            }
            return true;
        }

        if (is_string($privileges)) {
            // Check if string format is valid (#10001#10002#)
            $privilegeIds = array_filter(explode('#', $privileges));
            foreach ($privilegeIds as $privilegeId) {
                if (!is_numeric($privilegeId)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }
}
