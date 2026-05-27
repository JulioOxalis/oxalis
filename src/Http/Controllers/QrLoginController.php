<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Auth\LoginHandler;
use Oxalis\QrLogin\QrLoginService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class QrLoginController extends Controller
{
    public function __construct(
        private readonly QrLoginService $qr,
        private readonly LoginHandler   $login,
    ) {}

    public function show()
    {
        $token = $this->qr->generateToken();
        $ttl   = config('oxalis.qr_login.ttl', 90);
        $approveUrl = route('oxalis.qr.approve.show', $token);

        return view('oxalis::auth.qr-login', compact('token', 'ttl', 'approveUrl'));
    }

    /** AJAX polling — returns {status: 'pending'|'approved'|'expired'} */
    public function poll(string $token)
    {
        return response()->json($this->qr->check($token));
    }

    /** Desktop login-complete — called after poll detects 'approved' */
    public function login(Request $request, string $token)
    {
        $userId = $this->qr->consume($token);

        if (!$userId) {
            return response()->json(['error' => 'Token expired or not yet approved'], 422);
        }

        $userModel = config('oxalis.user_model');
        $user      = $userModel::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 422);
        }

        $redirect = $this->login->attempt(
            user:      $user,
            method:    'qr',
            ip:        $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json(['redirect' => $redirect->getTargetUrl()]);
    }

    /** Mobile approval page (must be authenticated) */
    public function showApprove(string $token)
    {
        $status = $this->qr->check($token);

        if ($status['status'] === 'expired') {
            return back()->withErrors(['qr' => 'This QR code has expired. Please scan a fresh one.']);
        }

        return view('oxalis::auth.qr-approve', compact('token'));
    }

    /** Mobile POSTs to approve */
    public function approve(Request $request, string $token)
    {
        if (!$this->qr->approve($token, auth()->id())) {
            return back()->withErrors(['qr' => 'Invalid or expired QR code.']);
        }

        return redirect()->route('oxalis.account')
            ->with('status', 'Desktop login approved — you can close this page.');
    }
}
