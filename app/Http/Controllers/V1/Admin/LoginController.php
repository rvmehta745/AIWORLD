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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     *   path="/register",
     *   tags={"Registration"},
     *   summary="Register a new user",
     *   operationId="register",
     *   
     *   @OA\RequestBody(
     *     required=true,
     *     description="User registration data",
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="first_name", type="string", example="John", description="First name (2-50 characters)"),
     *         @OA\Property(property="last_name", type="string", example="Doe", description="Last name (2-50 characters)"),
     *         @OA\Property(property="email", type="string", format="email", example="john.doe@example.com", description="Email address (unique)"),
     *         @OA\Property(property="password", type="string", format="password", example="Password123", description="Password (min 8 characters)"),
     *         @OA\Property(property="phone_number", type="string", example="1234567890", description="Phone number (optional)"),
     *         @OA\Property(property="country_code", type="string", example="+1", description="Country code (optional)")
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=201,
     *     description="User registered successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="SUCCESS"),
     *       @OA\Property(property="message", type="string", example="Registration successful. Please verify your email."),
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         @OA\Property(
     *           property="user",
     *           type="object",
     *           @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="first_name", type="string", example="John"),
     *           @OA\Property(property="last_name", type="string", example="Doe"),
     *           @OA\Property(property="email", type="string", example="john.doe@example.com")
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=400, description="Validation error"),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=500, description="Server error")
     * )
     */
    public function register(RegisterRequest $request)
    {
        Log::info('Register request received', ['request' => $request->all()]);
        try {
            // Get validated data
            $validated = $request->validated();

            // Generate verification token
            $verificationToken = sha1(time() . $validated['email'] . uniqid());

            // Check if a soft-deleted user with the same email exists
            $existingUser = User::withTrashed()->where('email', $validated['email'])->first();
            Log::info('Existing user check', ['existingUser' => $existingUser]);
            if ($existingUser && $existingUser->trashed()) {
                // Restore the soft-deleted user and update their information
                $existingUser->restore();

                $existingUser->update([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'password' => bcrypt($validated['password']),
                    'phone_number' => $validated['phone_number'] ?? null,
                    'country_code' => $validated['country_code'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'is_active' => false, // Set as inactive until email is verified
                    'email_verified_at' => null, // Reset email verification
                ]);
                Log::info('Restored user', ['user' => $existingUser]);
                $user = $existingUser;
            } else {
                // Create a new user with inactive status
                $user = User::create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'password' => bcrypt($validated['password']),
                    'phone_number' => $validated['phone_number'] ?? null,
                    'country_code' => $validated['country_code'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'is_active' => false, // Set as inactive until email is verified
                ]);
            }
            Log::info('New user created', ['user' => $user]);

            // Store verification token with 24-hour expiration
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => $verificationToken,
                    'created_at' => now(),
                    'expires_at' => now()->addHours(24)
                ]
            );
            Log::info('Verification token stored', ['email' => $user->email, 'token' => $verificationToken]);
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
     ** path="/login",
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
            $role = $user->role;
            $tokenType = 'bearer';
            $expiresIn = auth()->factory()->getTTL();
            return General::setResponse('SUCCESS', __('messages.login_successfully'), compact('role', 'token', 'tokenType', 'expiresIn'));
        } catch (Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     ** path="/forgot-password",
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
     ** path="/verify-otp",
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
     ** path="/reset-password",
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
     ** path="/change-password",
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
     *   path="/update-profile",
     *   tags={"Profile"},
     *   summary="Update user profile",
     *   operationId="updateProfile",
     *   security={{"bearer_token":{}}},
     *
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="first_name", type="string", description="min=2, max=50"),
     *         @OA\Property(property="last_name", type="string", description="min=2, max=50"),
     *         @OA\Property(property="email", type="string", format="email", description="email, unique, min=6, max=100"),
     *         @OA\Property(property="phone_number", type="string", description="nullable, min=10, max=15"),
     *         @OA\Property(property="country_code", type="string", description="nullable, max=20"),
     *         @OA\Property(property="address", type="string", description="nullable, max=255"),
     *         @OA\Property(property="photo", type="string", format="binary", description="nullable, image file (jpg,png,jpeg), stored in profile_photos")
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=400, description="Bad Request"),
     *   @OA\Response(response=404, description="Not Found"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=500, description="Server Error")
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
     ** path="/me",
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
     ** path="/logout",
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
     ** path="/verify-email",
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
