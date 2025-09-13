<?php

namespace App\Http\Middleware;

use App\Library\General;
use Closure;
use Illuminate\Http\Request;

class CheckUserPermission
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param array                    $permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $loggedInUser = $request->user();
        // Check user is logged in
        if (empty($loggedInUser)) {
            if (empty($request->bearerToken())) {
                return General::setResponse("NOT_LOGGED_IN");
            } else {
                return General::setResponse("SESSION_EXPIRED");
            }
        }

        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        $user = \DB::table('mst_users')->find($loggedInUser->id);
        
        // Super Admin has all permissions - bypass all checks
        if ($user->role === 'Super Admin') {
            return $next($request);
        }
        
        // Admin has all permissions but can be updated
        if ($user->role === 'Admin') {
            return $next($request);
        }
        
        $rolePrivilegeData = \DB::table('mst_roles')->whereNull('deleted_at')->where('name', $user->role)->first(['id', 'name', 'privileges']);

        if (empty($user->privileges)) {
            $userPrivileges = array_unique(array_filter(explode('#', $rolePrivilegeData->privileges)));
        } else {
            $userPrivileges = array_unique(array_filter(explode('#', $user->privileges)));
        }
        
        $privilegeList = [];
        if ($userPrivileges) {
            $privilegeList = \DB::table('lov_privileges')->select('id', 'group_id', 'name', 'path', 'permission_key')
                ->whereIn('id', $userPrivileges)
                ->where([
                    'is_active' => 1
                ])
                ->get()->pluck('permission_key')->toArray();
        }
        
        $hasPermission = array_intersect($permissions, $privilegeList);
        
        // Match user roles to requested roles

        if (empty($privilegeList) || empty($hasPermission)) {
            return General::setResponse("NO_PERMISSION");
        }

        return $next($request);
    }
}
