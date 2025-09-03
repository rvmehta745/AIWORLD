<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\V1\BaseController;
use App\Http\Requests\V1\Auth\UpdateProfileRequest;
use App\Http\Requests\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\V1\Auth\ForgotPasswordOtpRequest;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Http\Requests\V1\Auth\ResetPasswordViaLinkRequest;
use App\Http\Requests\V1\Auth\VerifyOtpRequest;
use App\Http\Requests\V1\Auth\VerifyAccountRequest;
use App\Library\General;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WelcomeEmailNotification;
use App\Notifications\LoginWelcomeNotification;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use App\Services\V1\UserService;
use Throwable;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Token;

class LoginController extends BaseController
{
    use CommonTrait;
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Post(
     ** path="/admin/register",
     *   tags={"Login"},
     *   summary="Register a new user",
     *   operationId="register",
     *
     *   @OA\Parameter(
     *      name="first_name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="last_name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="address",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="role",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=201,
     *       description="User registered successfully",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=422,
     *      description="Validation Error"
     *   ),
     *   @OA\Response(
     *      response=500,
     *      description="Server Error"
     *   )
     *)
     **/
    public function register(RegisterRequest $request)
    {
        try {

            // Get validated data
            $validated = $request->validated();
            
            // Generate verification token
            $verificationToken = sha1(time() . $validated['email'] . uniqid());
            
            // Check if a soft-deleted user with the same email exists
            $existingUser = User::withTrashed()->where('email', $validated['email'])->first();
            
            if ($existingUser && $existingUser->trashed()) {
                // Restore the soft-deleted user and update their information
                $existingUser->restore();
                
                $existingUser->update([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'password' => bcrypt($validated['password']),
                    'role' => $validated['role'],
                    'phone_number' => $validated['phone_number'] ?? null,
                    'country_code' => $validated['country_code'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'is_active' => false, // Set as inactive until email is verified
                    'email_verified_at' => null, // Reset email verification
                ]);
                
                $user = $existingUser;
            } else {
                // Create a new user with inactive status
                $user = User::create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'password' => bcrypt($validated['password']),
                    'role' => $validated['role'],
                    'phone_number' => $validated['phone_number'] ?? null,
                    'country_code' => $validated['country_code'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'is_active' => false, // Set as inactive until email is verified
                ]);
            }
            
           
            // Store verification token with 24-hour expiration
            \DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => $verificationToken,
                    'created_at' => now(),
                    'expires_at' => now()->addHours(24)
                ]
            );
            
            // Send welcome email with verification link
            $user->notify(new WelcomeEmailNotification($verificationToken));

            
            
            return General::setResponse("SUCCESS", __('messages.registration_successful_please_verify_email'), [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ]
            ], 201);
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }


    /**
     * @OA\Post(
     ** path="/admin/login",
     *   tags={"Login"},
     *   summary="Login",
     *   operationId="login",
     *
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
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
    public function authenticate(LoginRequest $request)
    {
        try {
            $postData = $request->all();

            $token = auth()->attempt(['email' => $postData['email'], 'password' => $postData['password']]);
            
            if (!$token) {
                $token = auth()->attempt(['phone_number' => $postData['email'], 'password' => $postData['password']]);
            }

            // Do auth
            if (!$token) {
                return General::setResponse("UNAUTHORIZED_LOGIN");
            }
            
            // Get authenticated user
            $user = auth()->user();
            // $this->createStripeCustomer($user);
            // Check if user is admin (ID 1) or verify status and email verification
            if ($user->id != 1) {
                // Check if email is verified
                if ($user->email_verified_at === null) {
                    auth()->logout();
                    return General::setResponse("OTHER_ERROR", __('messages.please_verify_your_email_before_login'));
                }
                
                // Check if user is active
                if (!$user->is_active) {
                    auth()->logout();
                    return General::setResponse("OTHER_ERROR", __('messages.account_inactive_or_blocked'));
                }
            }

            $tokenType = 'bearer';
            $expiresIn = auth()->factory()->getTTL();
            return General::setResponse('SUCCESS', __('messages.login_successfully'), compact('token', 'tokenType', 'expiresIn'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     ** path="/admin/forgot-password",
     *   tags={"Login"},
     *   summary="Request link for forgot password",
     *   operationId="link for forgot password",
     *
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
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
    protected function forgotPasswordOtp(ForgotPasswordOtpRequest $request)
    {
        try {

            \DB::beginTransaction();
            $this->userService->setOtp($request->all());
            \DB::commit();

            return General::setResponse("SUCCESS", __('messages.reset_password_link_sent_to_your_email_id'));
        } catch (Throwable $e) {
            \DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     ** path="/admin/verify-otp",
     *   tags={"Login"},
     *   summary="Verify OTP for password reset",
     *   operationId="verify-otp",
     *
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="otp",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
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
    protected function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $postData = $request->all();
            $otp = $this->userService->checkOtpExists($postData['otp']);
            
            if (empty($otp)) {
                return General::setResponse("OTHER_ERROR", __('messages.invalid_otp'));
            }
            
            $user = $this->userService->getUserByEmail($otp->email);
            if (empty($user)) {
                $user = $this->userService->getUserByMobileNo($otp->email);
            }

            if (empty($user)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.user')]));
            }
            
            if ($user->is_active != 1) {
                return General::setResponse("USER_DEACTIVATED");
            }
            
            // Return user email to be used in the reset password form
            return General::setResponse("SUCCESS", __('messages.otp_verified_successfully'), [
                'email' => $user->email
            ]);
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     ** path="/admin/reset-password",
     *   tags={"Login"},
     *   summary="Reset forgot password link for user.",
     *   operationId="reset-password",
     *
     *   @OA\Parameter(
     *      name="otp",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
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
    protected function resetPasswordViaLink(ResetPasswordViaLinkRequest $request)
    {

        try {
            $postData = $request->all();
           
            $user = $this->userService->getUserByEmail($postData['email']);
            
            if (empty($user)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.user')]));
            }
            
            if ($user->is_active != 1) {
                return General::setResponse("USER_DEACTIVATED");
            }

            \DB::beginTransaction();
            $this->userService->setPassword($user, $postData['password']);
            
            // Delete any existing OTP for this email
            $otp = $this->userService->checkOtpExistsByEmail($user->email);
           
            if (!empty($otp)) {
                $this->userService->deleteOtp($otp->token);
            }
            \DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_updated_successfully', ['moduleName' => __('labels.password')]));
        } catch (Throwable $e) {
            \DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     ** path="/admin/change-password",
     *   tags={"Login"},
     *   summary="Change user password with token after login.",
     *   operationId="change-password",
     *   security={{"bearer_token":{}}},
     *
     *   @OA\Parameter(
     *      name="current_password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
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
    protected function changePassword(ChangePasswordRequest $request)
    {
        try {
            $postData = $request->all();

            $user = $this->userService->getUserByEmail(\Auth::guard('api')->user()->email);
            if (empty($user)) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.user')]));
            }
            
            // Verify current password
            if (!\Hash::check($postData['current_password'], $user->password)) {
                return General::setResponse("OTHER_ERROR", __('messages.current_password_incorrect'));
            }

            \DB::beginTransaction();
            $this->userService->setPassword($user, $postData['password']);
            \DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_updated_successfully', ['moduleName' => __('labels.password')]));
        } catch (Throwable $e) {
            \DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/admin/update-profile",
     * tags={"Login"},
     * summary = "To update profile",
     * operationId = "To update profile for user",
     * security={{"bearer_token":{}}},
     *
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *              required={"first_name","last_name"},
     *              @OA\Property(
     *                property="first_name",
     *                description = "Validation: min=2,max=50",
     *                type="string",
     *             ),
     *             @OA\Property(
     *                property="last_name",
     *                description = "Validation: min=2,max=50",
     *                type="string",
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

    public function updateProfile(UpdateProfileRequest $request)
    {
       
        try {
            \DB::beginTransaction();
            $id = \Auth::user()->id;
            $data = $this->userService->updateProfile($id, $request);
            
            // Check if user was found
            if (!$data) {
                \DB::rollBack();
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.user')]));
            }
            
            \DB::commit();
            return General::setResponse("SUCCESS", __('messages.module_name_saved_successfully', ['moduleName' => __('labels.profile')]));
        } catch (Throwable $e) {
            \DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/admin/me",
     *   tags={"Login"},
     *   summary="Get user profile base details after login.",
     *   operationId="my PRofile",
     *   security={{"bearer_token":{}}},
     *
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
    protected function me()
    {
        try {
            $user = $this->userService->details(\Auth::user()->id);
            
            // Get max sales volume from manage_contact table
            // Add max sales volume to the response
            return General::setResponse("SUCCESS", [], [
                'user' => $user
            ]);
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/admin/profile-details",
     *   tags={"Login"},
     *   summary="Get detailed user profile information",
     *   operationId="profileDetails",
     *   security={{"bearer_token":{}}},
     *
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
    protected function profileDetails()
    {
        try {
            // Get authenticated user's ID
            $userId = \Auth::user()->id;
            
            // Get user details from repository
            $user = $this->userService->getUserProfileDetails($userId);
            
            if (!$user) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.user')]));
            }
            
            return General::setResponse("SUCCESS", [], compact('user'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/admin/logout",
     *   tags={"Login"},
     *   summary="Logout.",
     *   operationId="logout",
     *   security={{"bearer_token":{}}, {"x_localization":{}}},
     *
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
    public function logout()
    {
        try {
            auth()->logout();
            return General::setResponse("SUCCESS", __('messages.logout_successful'));
        } catch (\Exception $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     ** path="/admin/verify-email",
     *   tags={"Login"},
     *   summary="Verify user email address",
     *   operationId="verifyEmail",
     *
     *   @OA\Parameter(
     *      name="token",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Email verified successfully",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Invalid token"
     *   ),
     *   @OA\Response(
     *      response=500,
     *      description="Server Error"
     *   )
     *)
     **/
    public function verifyEmail(VerifyAccountRequest $request)
    {
        try {
            // Find the token in the database
            $tokenRecord = \DB::table('password_reset_tokens')
                ->where('token', $request->token)
                ->first();

            if (!$tokenRecord) {
                return General::setResponse("OTHER_ERROR", __('messages.invalid_token'));
            }
            
            // Check if token is expired (24 hours)
            if (isset($tokenRecord->expires_at) && now()->isAfter($tokenRecord->expires_at)) {
                return General::setResponse("OTHER_ERROR", __('messages.token_expired'));
            }

            // Find the user with this email
            $user = User::where('email', $tokenRecord->email)->first();

            if (!$user) {
                return General::setResponse("OTHER_ERROR", __('messages.module_name_not_found', ['moduleName' => __('labels.user')]));
            }

            \DB::beginTransaction();
            
            // Update user status to active and set email_verified_at
            $user->is_active = true;
            $user->email_verified_at = now();
            $user->save();
            
            // Delete the token
            \DB::table('password_reset_tokens')
                ->where('token', $request->token)
                ->delete();
                
            \DB::commit();

            return General::setResponse("SUCCESS", __('messages.email_verified_successfully'));
        } catch (Throwable $e) {
            \DB::rollBack();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    

    

    
}
