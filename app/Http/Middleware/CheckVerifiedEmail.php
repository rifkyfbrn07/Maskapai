<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVerifiedEmail
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (env('EMAIL_VERIFICATION_REQUIRED', true) === false) {
            return $next($request);
        }

        if ($request->user() && is_null($request->user()->email_verified_at)) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
