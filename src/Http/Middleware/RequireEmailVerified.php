<?php
namespace Oxalis\Http\Middleware;

use Oxalis\EmailVerification\EmailVerificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireEmailVerified
{
    public function __construct(private EmailVerificationService $service) {}

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !$this->service->isVerified(Auth::user())) {
            return redirect()->route('oxalis.email.notice');
        }

        return $next($request);
    }
}
