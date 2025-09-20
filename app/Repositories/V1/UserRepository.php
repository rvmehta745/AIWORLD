<?php

namespace App\Repositories\V1;

use App\Library\FunctionUtils;
use App\Mail\SendResetPasswordEmail;
use App\Models\LovPrivileges;
use App\Traits\CommonTrait;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Mail;
use App\Notifications\LoginWelcomeNotification;

class UserRepository extends BaseRepository
{
    use CommonTrait;

    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * List Team Member
     */
    public function list($postData, $page, $perPage)
    {
        $query = \DB::table('mst_users')
            ->whereNull('mst_users.deleted_at');

        if (!empty($postData['filter_data'])) {
            foreach ($postData['filter_data'] as $key => $value) {
                if (in_array($key, ["first_name", "last_name", "email", "phone_number", "display_status", 'role'])) {
                    switch ($key) {
                        case "display_status":
                            $key = \DB::raw('IF(mst_users.is_active=1,"' . __('labels.active') . '","' . __('labels.inactive') . '")');
                            break;
                        default:
                            $key = 'mst_users.' . $key;
                            break;
                    }
                    $query = $this->createWhere('text', $key, $value, $query);
                }


                if (in_array($key, ["created_at", "updated_at"])) {
                    $key   = 'mst_users.' . $key;
                    $query = $this->createWhere('date', $key, $value, $query);
                }

                if (in_array($key, ["is_active"])) {
                    // Handle the filter structure properly
                    if (isset($value['filter'])) {
                        if ($value['filter'] == 'Active') {
                            $value['values'] = [1];
                        } else {
                            $value['values'] = [0];
                        }
                    } else {
                        // Default to empty values if no filter provided
                        $value['values'] = [];
                    }
                    $key   = 'mst_users.' . $key;
                    $query = $this->createWhere('set', $key, $value, $query);
                }
            }
        }

        $query     = $query->select(
            'mst_users.id',
            'mst_users.first_name',
            'mst_users.last_name',
            'mst_users.email',
            'mst_users.phone_number',
            'mst_users.country_code',
            'mst_users.role',
            'mst_users.is_active',
            'mst_users.created_at',
        );
        $orderBy   = 'mst_users.updated_at';
        $orderType = (isset($postData['order_by']) && $postData['order_by'] == 1) ? 'asc' : 'desc';
        if (!empty($postData['sort_data'])) {
            $orderBy   = $postData['sort_data'][0]['colId'];
            $orderType = $postData['sort_data'][0]['sort'];
        }
        $query       = $query->orderBy($orderBy, $orderType);
        $count       = $query->count();
        $dataPerPage = $query->skip($page)->take($perPage)->get()->toArray();
        return ['data' => $dataPerPage, 'count' => $count];
    }

    /**
     * Store Team Member
     */
    public function store($request)
    {

        // Split name into first_name and last_name

        $storeData = [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'phone_number'  => $request->phone_number ?? null,
            'country_code'  => $request->country_code ?? null,
            'email'      => $request->email,
            'password'   => bcrypt($request->password),
            'role'       => $request->role,
            'email_verified_at' => now(),
            'is_active'  => true,
            'created_by'  => \Auth::user()->id,
            'updated_by'  => \Auth::user()->id,
        ];
        $user = User::create($storeData);

        // Send welcome email notification
        $user->notify(new LoginWelcomeNotification());

        return $user;
    }

    /**
     * Details Team Member
     */
    public function details($id)
    {
        $menu = [];
        // $me   = $this->user->find($id);
        $me = $this->user
            ->select('id', 'first_name', 'last_name', 'email', 'phone_number', 'country_code', 'address', 'photo', 'role')
            ->where('id', $id)
            ->first();

        if (empty($me)) {
            return null;
        }

        $user = (object)$me;

        $rolePrivilegeData = $me->role()->select(['id', 'name', 'privileges'])->first();

        if (empty($user->privileges)) {
            $userPrivileges = array_unique(array_filter(explode('#', $rolePrivilegeData->privileges)));
        } else {
            $userPrivileges = array_unique(array_filter(explode('#', $user->privileges)));
        }

        $userPrivilegesKey = [];
        $temp              = [];
        if ($userPrivileges) {

            $lovPrivileges = LovPrivileges::select('id', 'parent_id', 'group_id', 'name as label', 'path', 'permission_key')
                ->whereIn('id', $userPrivileges)
                ->where('is_active', 1)
                ->orderBy('sequence')
                ->get();


            foreach ($lovPrivileges as $privileges) {
                $userPrivilegesKey[] = $privileges->permission_key;

                // Exclude unwanted privilege labels from the menu
                if (in_array($privileges->permission_key, ['PROFILE', 'PROFILE_INDEX', 'PROFILE_UPDATE', 'TERMS_CONDITIONS', 'TERMS_CONDITIONS_INDEX'])) {
                    continue;
                }

                // Exclude privileges with empty paths from menu
                if (empty(trim($privileges->path))) {
                    continue;
                }

                if ($privileges->parent_id == 0) {
                    $groupId = $privileges->group_id;
                    if (empty($groupId)) {
                        $menu[$privileges->id]['id']        = $privileges->id;
                        $menu[$privileges->id]['label']     = $privileges->label;
                        $menu[$privileges->id]['path']      = $privileges->path;
                        $menu[$privileges->id]['parent_id'] = $privileges->parent_id;
                        $menu[$privileges->id]['group_id']  = $privileges->group_id;
                        $menu[$privileges->id]['child']     = $privileges->child()->get();
                    } else {
                        $group                        = $privileges->group()->first(['id', 'name']);
                        $menu[$groupId]['id']         = $privileges->id;
                        $menu[$groupId]['label']      = $group->name;
                        $menu[$groupId]['path']       = $privileges->id;
                        $menu[$groupId]['parent_id']  = $privileges->parent_id;
                        $menu[$groupId]['group_id']   = $privileges->group_id;
                        $menu[$groupId]['child']      = $privileges->child()->get();
                        $menu[$groupId]['children'][] = $privileges;
                    }
                }
            }

            sort($userPrivilegesKey);
        }

        // $user->temp           = $temp;
        $user->userPrivileges = $userPrivilegesKey;
        $user->role           = $rolePrivilegeData;
        $menu                 = array_values($menu);
        $user->menu           = collect($menu)->sortBy('name')->values();

        //        $user->utilities_menu = $this->utilities_menu($userPrivileges);

        // Add photo URL if photo exists
        if ($user->photo) {
            $user->photo = asset('storage/profile_photos/' . $user->photo);
        }

        return $user;
    }
    /**
     * Details Team Member
     */
    public function detailsByID($id)
    {
        $me = $this->user
            ->select('id', 'first_name', 'last_name', 'email', 'phone_number', 'country_code', 'address', 'role')
            ->where('id', $id)
            ->first();

        if (empty($me)) {
            return null;
        }


        return $me;
    }

    /**
     * Update Team Member
     */
    public function update($id, $request)
    {
        $data = $this->user->find($id);

        $updateData = [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'phone_number'  => $request['phone_number'] ?? null,
            'country_code'  => $request['country_code'] ?? null,
            'email'      => $request['email'],
            'updated_by' => \Auth::user()->id,
        ];

        $data->update($updateData);
        return $data;
    }

    /**
     * Update user Profile
     * */
    public function updateProfile($id, $request)
    {

        // Try to find the user by ID
        $data = $this->user->find($id);

        // If user not found, try to find by authenticated user
        if (!$data && \Auth::check()) {
            $data = \Auth::user();
        }

        // If still no user found, return null
        if (!$data) {
            return null;
        }

        // Update basic info
        $data->first_name = $request->first_name;
        $data->last_name = $request->last_name;
        $data->updated_by = $id;

        // Update address
        if ($request->has('address')) {
            $data->address = $request->address;
        }

        // Handle photo upload
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            // Delete old photo if exists
            if ($data->photo && file_exists(storage_path('app/public/profile_photos/' . $data->photo))) {
                unlink(storage_path('app/public/profile_photos/' . $data->photo));
            }

            // Generate unique filename
            $fileName = time() . '_' . $request->file('photo')->getClientOriginalName();

            // Store the file
            $request->file('photo')->storeAs('public/profile_photos', $fileName);

            // Save filename to database
            $data->photo = $fileName;
        }

        $data->save();

        // Add photo URL to response if photo exists
        if ($data->photo) {
            $data->photo_url = asset('storage/profile_photos/' . $data->photo);
        }

        return $data;
    }

    /**
     * Delete Team Member
     */
    public function destroy($id)
    {
        return $this->user->find($id)->delete();
    }

    /**
     * Team Member Status Change
     */
    public function changeStatus($id, $request)
    {
        $data = $this->user->find($id);
        $data->update([
            'is_active' => $request->is_active == 1 ? true : false,
        ]);

        return $data;
    }

    /**
     * Set Otp for user
     */
    public function setOtp($postData)
    {
        do {
            $otp      = strtoupper(CommonTrait::getOtpForUser(4));
            $otpFound = \DB::table('password_reset_tokens')->where([
                'token' => $otp,
            ])->count();
        } while ($otpFound != 0);

        \DB::table('password_reset_tokens')->where([
            'email' => $postData['email'],
        ])->delete();

        $resetPassword = \DB::table('password_reset_tokens')->insert([
            'email'      => $postData['email'],
            'token'      => $otp,
            'created_at' => \Carbon\Carbon::now(),
        ]);
        $user          = User::where('email', $postData['email'])->first();
        $templateData  = [
            'name'    => $user->first_name . ' ' . $user->last_name,
            'url' => env('APP_URL') . "verify-otp/" . base64_encode($user->email),
            'otp'     => $otp,
            'email'   => $user->email,
        ];

        // dispatch(new \App\Jobs\SendTemplateEmailJob("FORGOT_PASSWORD", $templateData));
        Mail::to($templateData['email'])->send(new SendResetPasswordEmail($templateData));
    }

    /**
     * Check Otp Exists
     */
    public function checkOtpExists($otp)
    {
        return \DB::table('password_reset_tokens')->where([
            'token' => $otp,
        ])->first();
    }

    /**
     * Check Otp Exists By Email
     */
    public function checkOtpExistsByEmail($email)
    {
        return \DB::table('password_reset_tokens')->where([
            'email' => $email,
        ])->first();
    }

    /**
     * Get User by email
     */
    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    /**
     * Get User by phone number
     */
    public function getUserByMobileNo($mobileNo)
    {
        return User::where('phone_number', $mobileNo)->first();
    }

    /**
     * Set Password
     */
    public function setPassword($user, $password)
    {
        $user->update([
            'password' => bcrypt($password),
        ]);
        return $user;
    }

    /**
     * Get User permission by User Id
     */
    public function deleteOtp($otp)
    {
        \DB::table('password_reset_tokens')->where([
            'token' => $otp,
        ])->delete();
    }

    /**
     * Get user profile details
     *
     * @param int $userId
     * @return mixed
     */
    public function getUserProfileDetails($userId)
    {
        $user = $this->user->find($userId);

        if (!$user) {
            return null;
        }

        // Get only the profile-related information
        $profileData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'country_code' => $user->country_code,
            'address' => $user->address,
            'photo' => $user->photo,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];

        // Add photo URL if photo exists
        if ($user->photo) {
            $profileData['photo_url'] = asset('storage/profile_photos/' . $user->photo);
        }

        return $profileData;
    }

    /**
     * Get total number of users
     *
     * @return int
     */
    public function getTotalUserCount()
    {
        return $this->user->whereNull('deleted_at')->count();
    }

    /**
     * Get total number of active users
     *
     * @return int
     */
    public function getActiveUserCount()
    {
        return $this->user->whereNull('deleted_at')->where('is_active', true)->count();
    }

    /**
     * Get all active users for dropdown
     *
     * @return mixed
     */
    public function getAllActiveUsers()
    {
        return $this->user
            ->select('id', 'first_name', 'last_name', 'email', 'role')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('first_name', 'asc')
            ->get();
    }
}
