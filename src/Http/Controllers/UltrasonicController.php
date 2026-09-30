<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Auth\LoginHandler;
use Oxalis\Ultrasonic\UltrasonicService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UltrasonicController extends Controller
{
    public function __construct(
        private readonly UltrasonicService $service,
        private readonly LoginHandler      $login,
    ) {}

    /** Desktop: get a fresh token to transmit */
    public function begin()
    {
        return response()->json($this->service->generateToken());
    }

    /** Desktop: poll until approved or expired */
    public function poll(Request $request, string $token)
    {
        $data = $this->service->check($token);

        if ($data['status'] !== 'approved') {
            return response()->json(['status' => $data['status']]);
        }

        $userId = $this->service->consume($token);
        if (!$userId) {
            return response()->json(['status' => 'expired']);
        }

        $userModel = config('oxalis.user_model');
        $user      = $userModel::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 422);
        }

        $redirect = $this->login->attempt(
            user:      $user,
            method:    'ultrasonic',
            ip:        $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json(['status' => 'approved', 'redirect' => $redirect->getTargetUrl()]);
    }

    /** Mobile: receive decoded token + authenticate the desktop session */
    public function approve(Request $request)
    {
        $request->validate(['token' => 'required|string|size:8|regex:/^[A-Fa-f0-9]{8}$/']);

        if (!$this->service->approve($request->input('token'), auth()->id())) {
            return response()->json(['error' => 'Token expired or already used'], 422);
        }

        return response()->json(['message' => 'Approved — the other device will log in momentarily.']);
    }

    /** Mobile listen page (must be authenticated) */
    public function showListen()
    {
        return view('oxalis::auth.ultrasonic-listen');
    }
}
