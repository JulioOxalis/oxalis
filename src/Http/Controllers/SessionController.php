<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\OxalisSession;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function index()
    {
        $currentToken = session('oxalis_session_token');
        $currentHash  = $currentToken ? hash('sha256', $currentToken) : null;

        try {
            $sessions = OxalisSession::where('user_id', Auth::id())
                ->latest('last_active_at')
                ->get()
                ->map(fn($s) => [
                    'id'          => $s->id,
                    'label'       => $s->device_label ?? 'Unknown device',
                    'ip'          => $s->ip_address ?? '—',
                    'method'      => $s->method ?? '—',
                    'last_active' => $s->last_active_at?->diffForHumans() ?? 'Unknown',
                    'created'     => $s->created_at?->diffForHumans() ?? '—',
                    'is_current'  => $currentHash && $s->token === $currentHash,
                ]);
        } catch (\Throwable) {
            $sessions = collect();
        }

        return view('oxalis::account.sessions', compact('sessions'));
    }

    public function revoke(Request $request)
    {
        $request->validate(['session_id' => 'required']);

        try {
            $session = OxalisSession::where('id', $request->session_id)
                ->where('user_id', Auth::id())
                ->first();

            if ($session) {
                $currentToken = session('oxalis_session_token');
                $isCurrent = $currentToken && $session->token === hash('sha256', $currentToken);

                $session->delete();

                if ($isCurrent) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('oxalis.login')
                        ->with('status', 'Your current session was revoked.');
                }
            }
        } catch (\Throwable) {}

        return back()->with('status', 'Session revoked.');
    }

    public function revokeAll(Request $request)
    {
        try {
            OxalisSession::revokeAllForUser(Auth::id());
        } catch (\Throwable) {}

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('oxalis.login')
            ->with('status', 'All sessions have been signed out.');
    }
}
