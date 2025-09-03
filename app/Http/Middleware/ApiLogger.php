<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ApiLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): HttpResponse
    {
        // Skip logging for register endpoint
        if ($request->is('*/register')) {
            return $next($request);
        }

        $startTime = microtime(true);
        
        // Create initial log entry
        $logId = $this->createInitialLog($request);
        
        // Get the response
        $response = $next($request);
        
        // Update log immediately after response is generated
        if ($logId) {
            $this->updateLogEntry($logId, $startTime, $request, $response);
        }

        return $response;
    }

    /**
     * Terminate method - called after response is sent (backup)
     */
    public function terminate(Request $request, HttpResponse $response): void
    {
        // Backup method - not needed with immediate update
    }

    /**
     * Create initial log entry
     */
    private function createInitialLog(Request $request): ?int
    {
        try {
            $log = ApiLog::create([
                'user_id' => Auth::id(),
                'http_method' => $request->method(),
                'api_endpoint' => $request->getPathInfo(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_body' => $this->getRequestBody($request),
                'response_status_code' => 0, // Will be updated later
                'created_by' => Auth::id()
            ]);
            
            return $log->id;
        } catch (\Exception $e) {
            \Log::error('Failed to create initial API log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update log entry with response data
     */
    private function updateLogEntry(int $logId, float $startTime, Request $request, HttpResponse $response): void
    {
        try {
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000);
            $statusCode = $response->getStatusCode();
            $isError = $statusCode >= 400;
            
            $responseBody = null;
            $errorMessage = null;
            $extractedUserId = null;
            
            $content = $response->getContent();
            if ($content && $this->isJson($content)) {
                $responseBody = json_decode($content, true);
                
                if ($isError && isset($responseBody['message'])) {
                    $errorMessage = $responseBody['message'];
                }
                
                // Extract user_id for auth endpoints
                if ($this->isAuthEndpoint($request)) {
                    $extractedUserId = $this->getUserIdForAuthEndpoint($request, $responseBody, $statusCode);
                }
            }

            $updateData = [
                'response_status_code' => $statusCode,
                'response_body' => $responseBody,
                'duration_ms' => $duration,
                'is_error' => $isError,
                'error_message' => $errorMessage,
                'updated_by' => Auth::id(),
            ];
            
            // Update user_id if extracted from auth response
            if ($extractedUserId) {
                $updateData['user_id'] = $extractedUserId;
            }

            ApiLog::where('id', $logId)->update($updateData);
        } catch (\Exception $e) {
            \Log::error('Failed to update API log: ' . $e->getMessage());
        }
    }

    /**
     * Check if endpoint is an auth endpoint
     */
    private function isAuthEndpoint(Request $request): bool
    {
        $authEndpoints = ['login', 'register', 'logout'];
        $path = $request->getPathInfo();
        
        foreach ($authEndpoints as $endpoint) {
            if (str_contains($path, $endpoint)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get user_id for auth endpoints based on endpoint type and response
     */
    private function getUserIdForAuthEndpoint(Request $request, ?array $responseBody, int $statusCode): ?int
    {
        $path = $request->getPathInfo();
        
        // For successful responses only
        if ($statusCode >= 400) {
            return null;
        }
        
        // Register endpoint - extract from response
        if (str_contains($path, 'register')) {
            return $this->extractUserIdFromResponse($responseBody);
        }
        
        // Login endpoint - get from Auth after successful login
        if (str_contains($path, 'login')) {
            $userId = Auth::id();
            // If Auth::id() is not available, try to extract from JWT token in response
            if (!$userId && isset($responseBody['token'])) {
                try {
                    $tokenParts = explode('.', $responseBody['token']);
                    if (count($tokenParts) === 3) {
                        $payload = json_decode(base64_decode($tokenParts[1]), true);
                        if (isset($payload['sub'])) {
                            $userId = (int) $payload['sub'];
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore token parsing errors
                }
            }
            return $userId;
        }
        
        // Logout endpoint - get from Auth before logout
        if (str_contains($path, 'logout')) {
            return Auth::id(); // User is still authenticated during logout process
        }
        
        return null;
    }
    
    /**
     * Extract user_id from response for register endpoint
     */
    private function extractUserIdFromResponse(?array $responseBody): ?int
    {
        if (!$responseBody) {
            return null;
        }
        
        // Check various possible locations for user_id
        if (isset($responseBody['data']['user']['id'])) {
            return $responseBody['data']['user']['id'];
        }
        
        if (isset($responseBody['user']['id'])) {
            return $responseBody['user']['id'];
        }
        
        if (isset($responseBody['data']['id'])) {
            return $responseBody['data']['id'];
        }
        
        if (isset($responseBody['user_id'])) {
            return $responseBody['user_id'];
        }
        
        return null;
    }

    /**
     * Get request body data
     */
    private function getRequestBody(Request $request): ?array
    {
        $body = $request->all();
        
        // Remove sensitive data
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key'];
        foreach ($sensitiveFields as $field) {
            if (isset($body[$field])) {
                $body[$field] = '[REDACTED]';
            }
        }
        
        return empty($body) ? null : $body;
    }

    /**
     * Check if string is valid JSON
     */
    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}