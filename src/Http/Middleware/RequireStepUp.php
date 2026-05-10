<?php
namespace Oxalis\Http\Middleware;

use Oxalis\StepUp\StepUpService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireStepUp
{
    public function __construct(private readonly StepUpService $stepUp) {}

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('oxalis.login');
        }

        if (!$this->stepUp->isVerified()) {
            return $this->stepUp->challenge($request->url());
        }

        return $next($request);
    }
}
