<?php

namespace App\Http\Middleware;

use App\Library\General;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateUser
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param array   $userType
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$userType): Response
    {
        if (empty(\Auth::user())) {
            if (empty($request->bearerToken())) {
                return General::setResponse("NOT_LOGGED_IN");
            } else {
                return General::setResponse("SESSION_EXPIRED");
            }
        }
        return $next($request);
    }
}
