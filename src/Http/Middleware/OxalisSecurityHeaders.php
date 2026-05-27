<?php
namespace Oxalis\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OxalisSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!config('oxalis.security_headers', true)) {
            return $response;
        }

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        // Allow microphone for ultrasonic auth on own origin; deny camera/geo
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(self), geolocation=()');

        return $response;
    }
}
