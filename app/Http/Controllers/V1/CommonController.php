<?php

namespace App\Http\Controllers\V1;

use App\Library\General;
use App\Models\Country;
use App\Models\Industry;
use App\Models\State;
use App\Models\ApiLog;
use App\Services\V1\CommonService;
use Throwable;
use Illuminate\Http\Request;

class CommonController extends BaseController
{
    public function __construct(protected CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    /**
     * @OA\Post(
     ** path="/sync",
     *   tags={"Common"},
     *   summary="Sync Data",
     *   operationId="common-sync-data",
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
    public function iterateKeys(&$inputArray, $tmp = null, $name = '')
    {
        if ($tmp === null) {
            $tmp = $inputArray;
        }

        foreach ($tmp as $index => $value) {
            if (is_array($value)) {
                $this->iterateKeys($inputArray, $value, $name . '_' . $index);
            } else {
                $translation = $value;
                if (\Str::contains($translation, ':')) {
                    $arr = [];
                    preg_match_all('/\B:\w+/i', $translation, $arr);
                    $value = $translation;
                    if (sizeof($arr) > 0) {
                        foreach ($arr[0] as $v) {
                            $cleanMatchedStr = str_replace(':', '', $v);
                            $value           = str_replace($v, '{{' . $cleanMatchedStr . '}}', $value);
                        }
                    }
                    $translation = $value;
                }

                $inputArray[$name . '_' . $index] = $translation;
            }

            if (isset($inputArray[$index])) {
                unset($inputArray[$index]);
            }
        }

        return $inputArray;
    }


    public function sync(Request $request)
    {
        try {
            $files = collect(\File::files(lang_path('en/')));

            $translationData = $files->reduce(function ($trans, $file) {
                $translations = require($file);
                $trans[]      = $this->iterateKeys($translations, null, str_replace(".php", "", basename($file)));
                return $trans;
            }, []);
            $translationData = array_merge(...$translationData);
            $status          = array_values(config('global.STATUS'));

            return General::setResponse("SUCCESS", [], compact('translationData', 'status'));
        } catch (\Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     ** path="/language/{local_key}",
     *   tags={"Common"},
     *   summary="Language translation Data for web",
     *   operationId="common-language-translation-data-for-web",
     *
     *   @OA\Parameter(
     *      name="local_key",
     *      in="path",
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
    public function languageTranslationData($localKey)
    {
        try {
            $files = collect(\File::files(lang_path($localKey . '/')));

            $translationData = $files->reduce(function ($trans, $file) {
                $translations = require($file);
                $trans[]      = $this->iterateKeys($translations, null, str_replace(".php", "", basename($file)));
                return $trans;
            }, []);
            $translationData = array_merge(...$translationData);

            return response()->json($translationData, 200);
        } catch (\Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    public function callManualCommand(Request $request)
    {
        if (!empty($request->my_command)) {
            try {
                $result['status'] = 200;
                foreach ($request->my_command as $item) {
                    \Artisan::call($item, []);
                }
                $result['message'] = "Command Run successfully.";
            } catch (\Throwable $e) {
                $result['status'] = 422;
                $result['errors'] = $e->getMessage();
            }
            return response()->json($result, $result['status']);
        }
        return response()->json(['status' => 200, 'message' => "Not Found."]);
    }

    public function viewLog(Request $request)
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            if (!empty($request->clear)) {
                exec('echo "" > ' . $logPath);
            }

            $result['status']  = 200;
            $result['message'] = file_get_contents($logPath);
        } catch (\Throwable $e) {
            $result['status'] = 422;
            $result['errors'] = $e->getMessage();
        }
        return response()->json(['status' => 200, 'message' => $result]);
    }

    /**
     * @OA\Get(
     * path="/privileges-list",
     * tags = {"Common"},
     * summary = "To get the list of privileges",
     * operationId = "To get the list of privileges",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
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
    public function privilegesList(Request $request)
    {
        try {
            \DB::beginTransaction();
            $data = $this->commonService->privilegesList($request);
            \DB::commit();
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            \DB::rollback();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }
    /**
     * @OA\Get(
     * path="/roles-list",
     * tags = {"Common"},
     * summary = "To get the list of roles",
     * operationId = "To get the list of roles",
     * security={{"bearer_token":{}}, {"x_localization":{}}},
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
    public function rolesList(Request $request)
    {
        try {
            \DB::beginTransaction();
            $data = $this->commonService->roles($request);
            \DB::commit();
            return General::setResponse("SUCCESS", [], compact('data'));
        } catch (Throwable $e) {
            \DB::rollback();
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }

    /**
     * This Method Use for Developer purpose for run command manually
     */
    public function callManuallCommand(Request $request)
    {
        // Define the expected header key and value
        $expectedKey = 'seva';
        $expectedValue = 'Mahakumbh';

        // Check if the header exists and matches the expected value
        if ($request->header($expectedKey) !== $expectedValue) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized access.'
            ], 403);
        }
        if ($request->mycommand) {
            try {
                $result['status'] = 200;
                \Artisan::call($request->mycommand, []);
                $result['message'] = $request->mycommand . ' ran successfully.';
                $result['output'] = \Artisan::output();
            } catch (\Exception $e) {
                $result['status'] = 422;
                $result['errors'] = $e->getMessage();
            }
            return response()->json($result, $result['status']);
        }
        return response()->json(['status' => 200, 'message' => "Command not found."]);
    }

    /**
     * @OA\Get(
     *     path="/api-logs",
     *     tags={"API logs"},
     *     summary="View API logs",
     *     description="Retrieve API logs with filtering options",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="endpoint",
     *         in="query",
     *         description="Filter by API endpoint (partial match)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="method",
     *         in="query",
     *         description="Filter by HTTP method",
     *         required=false,
     *         @OA\Schema(type="string", enum={"GET", "POST", "PUT", "DELETE", "PATCH"})
     *     ),
     *     @OA\Parameter(
     *         name="status_code",
     *         in="query",
     *         description="Filter by response status code",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_error",
     *         in="query",
     *         description="Filter by error status",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="SUCCESS"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="user_id", type="integer"),
     *                         @OA\Property(property="http_method", type="string"),
     *                         @OA\Property(property="api_endpoint", type="string"),
     *                         @OA\Property(property="ip_address", type="string"),
     *                         @OA\Property(property="response_status_code", type="integer"),
     *                         @OA\Property(property="duration_ms", type="integer"),
     *                         @OA\Property(property="is_error", type="boolean"),
     *                         @OA\Property(property="created_at", type="string", format="date-time")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     * )
     */
    public function viewApiLogs(Request $request)
    {
        try {
            $query = ApiLog::with('user:id,first_name,last_name,email')
                ->orderBy('created_at', 'desc');

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('endpoint')) {
                $query->where('api_endpoint', 'like', '%' . $request->endpoint . '%');
            }

            if ($request->has('method')) {
                $query->where('http_method', $request->method);
            }

            if ($request->has('status_code')) {
                $query->where('response_status_code', $request->status_code);
            }

            if ($request->has('is_error')) {
                $query->where('is_error', $request->boolean('is_error'));
            }

            $perPage = $request->get('per_page', 50);
            $logs = $query->paginate($perPage);

            return General::setResponse("SUCCESS", [], $logs);
        } catch (\Throwable $e) {
            return General::setResponse("EXCEPTION", $e->getMessage());
        }
    }
}
