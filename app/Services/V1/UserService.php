<?php

namespace App\Services\V1;

use App\Repositories\V1\UserRepository;
use App\Services\BaseService;

class UserService extends BaseService
{

    private UserRepository $userRepository;
    public function __construct()
    {
        $this->userRepository = new UserRepository;
    }

    /**
     * Set Otp
     */
    public function setOtp($postData)
    {
        return $this->userRepository->setOtp($postData);
    }

    /**
     * Check Otp Exists
     */
    public function checkOtpExists($otp)
    {
        return $this->userRepository->checkOtpExists($otp);
    }

    /**
     * Get User by email
     */
    public function getUserByEmail($email)
    {
        return $this->userRepository->getUserByEmail($email);
    }

    /**
     * Get User by mobileNo
     */
    public function getUserByMobileNo($mobileNo)
    {
        return $this->userRepository->getUserByMobileNo($mobileNo);
    }

    /**
     * Set Password
     */
    public function setPassword($user, $password)
    {
        return $this->userRepository->setPassword($user, $password);
    }

    /**
     *
     */
    public function deleteOtp($otp)
    {
        return $this->userRepository->deleteOtp($otp);
    }

    /**
     * List User
     */
    public function list($postData, $page, $perPage)
    {
        return $this->userRepository->list($postData, $page, $perPage);
    }

    /**
     * User Store
     */
    public function store($request)
    {

        return $this->userRepository->store($request);
    }

    /**
     * User Details
     */
    public function details($request)
    {
        return $this->userRepository->details($request);
    }
    /**
     * User Details By ID
     */
    public function detailsByID($id)
    {
        return $this->userRepository->detailsByID($id);
    }

    /**
     * User Update
     */
    public function update($id, $request)
    {
        return $this->userRepository->update($id, $request);
    }

    /**
     * User Delete
     */
    public function destory($id)
    {
        return $this->userRepository->destroy($id);
    }

    /**
     * User Status Change
     */
    public function changeStatus($id, $request)
    {
        return $this->userRepository->changeStatus($id, $request);
    }

    /**
     * Update user Profile
     * */
    public function updateProfile($id, $request)
    {
        return $this->userRepository->updateProfile($id, $request);
    }

     /**
     * Get User by email
     */
    public function checkOtpExistsByEmail($email)
    {
        return $this->userRepository->checkOtpExistsByEmail($email);
    }

    /**
     * Get user profile details
     *
     * @param int $userId
     * @return mixed
     */
    public function getUserProfileDetails($userId)
    {
        return $this->userRepository->getUserProfileDetails($userId);
    }

    /**
     * Get total number of users
     *
     * @return int
     */
    public function getTotalUserCount()
    {
        return $this->userRepository->getTotalUserCount();
    }

    /**
     * Get total number of active users
     *
     * @return int
     */
    public function getActiveUserCount()
    {
        return $this->userRepository->getActiveUserCount();
    }
}
