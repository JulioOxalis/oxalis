<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\AuthEvent;
use Oxalis\Models\Lockout;
use Oxalis\Models\Passkey;
use Oxalis\Models\TotpSecret;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class AdminController extends Controller
{
    public function index()
    {
        abort_unless(config('oxalis.admin.enabled', false), 404);

        if ($gate = config('oxalis.admin.gate')) {
            abort_unless(Gate::allows($gate), 403, 'Access denied.');
        }

        $userModel = config('oxalis.user_model');

        $users = $userModel::latest()->paginate(50);

        $uids = $users->pluck($users->first()?->getKeyName() ?? 'id')->map(fn($id) => (string) $id)->toArray();

        // PHP-level aggregation — compatible with both MySQL and MongoDB
        $passkeyCounts = Passkey::whereIn('user_id', $uids)
            ->get(['user_id'])
            ->groupBy('user_id')
            ->map(fn($g) => $g->count());

        $totpEnabled = TotpSecret::whereIn('user_id', $uids)
            ->whereNotNull('confirmed_at')
            ->get(['user_id'])
            ->pluck('user_id')
            ->map(fn($id) => (string) $id)
            ->flip();

        $lastLogin = AuthEvent::whereIn('user_id', $uids)
            ->where('status', 'success')
            ->get(['user_id', 'created_at'])
            ->groupBy('user_id')
            ->map(fn($g) => $g->max('created_at'));

        $recentLockouts = Lockout::where('attempts', '>', 0)
            ->latest('updated_at')
            ->take(20)
            ->get();

        $recentEvents = AuthEvent::latest()->take(20)->get();

        $totalLogins  = AuthEvent::where('status', 'success')->count();
        $totalFailed  = AuthEvent::where('status', '!=', 'success')->count();
        $lockedNow    = Lockout::where('locked_until', '>', now())->count();

        return view('oxalis::admin.index', compact(
            'users', 'passkeyCounts', 'totpEnabled', 'lastLogin',
            'recentLockouts', 'recentEvents', 'totalLogins', 'totalFailed', 'lockedNow',
        ));
    }
}
