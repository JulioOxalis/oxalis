<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\AdminCredential;
use Oxalis\Models\AuthEvent;
use Oxalis\Models\Lockout;
use Oxalis\Models\Passkey;
use Oxalis\Models\TotpSecret;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $userModel = config('oxalis.user_model');
        $search    = trim($request->get('search', ''));
        $filter    = $request->get('filter', 'all');

        $query = $userModel::query();

        // Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(50)->withQueryString();

        $uids = $users->pluck(
            $users->first()?->getKeyName() ?? 'id'
        )->map(fn($id) => (string) $id)->toArray();

        // Cached aggregate stats — 5-minute TTL, safe for large datasets
        $passkeyCounts = Cache::remember('oxalis_admin_passkeys_' . md5(implode(',', $uids)), 300,
            fn() => Passkey::whereIn('user_id', $uids)->get(['user_id'])
                ->groupBy('user_id')->map(fn($g) => $g->count())
        );

        $totpEnabled = Cache::remember('oxalis_admin_totp_' . md5(implode(',', $uids)), 300,
            fn() => TotpSecret::whereIn('user_id', $uids)->whereNotNull('confirmed_at')
                ->get(['user_id'])->pluck('user_id')->map(fn($id) => (string) $id)->flip()
        );

        $lastLogin = Cache::remember('oxalis_admin_last_' . md5(implode(',', $uids)), 300,
            fn() => AuthEvent::whereIn('user_id', $uids)->where('status', 'success')
                ->get(['user_id', 'created_at'])
                ->groupBy('user_id')->map(fn($g) => $g->max('created_at'))
        );

        // Apply filter after fetching (keeps pagination correct for search)
        if ($filter === 'passkey') {
            $uids = $users->filter(fn($u) => ($passkeyCounts[(string)$u->getAuthIdentifier()] ?? 0) > 0)
                ->pluck($users->first()?->getKeyName() ?? 'id')->map(fn($id) => (string)$id)->toArray();
        } elseif ($filter === 'totp') {
            $uids = $users->filter(fn($u) => isset($totpEnabled[(string)$u->getAuthIdentifier()]))
                ->pluck($users->first()?->getKeyName() ?? 'id')->map(fn($id) => (string)$id)->toArray();
        }

        // Global stats (cached for 60 seconds)
        [$totalLogins, $totalFailed, $lockedNow, $totalUsers, $totalPasskeys] = Cache::remember('oxalis_admin_global', 60, function () use ($userModel) {
            try {
                return [
                    AuthEvent::where('status', 'success')->count(),
                    AuthEvent::where('status', '!=', 'success')->count(),
                    Lockout::where('locked_until', '>', now())->count(),
                    $userModel::count(),
                    Passkey::count(),
                ];
            } catch (\Throwable) {
                return [0, 0, 0, 0, 0];
            }
        });

        $cred         = AdminCredential::first();
        $recentEvents = AuthEvent::latest()->take(15)->get();
        $recentLockouts = Lockout::where('attempts', '>', 0)->latest('updated_at')->take(10)->get();

        return view('oxalis::admin.index', compact(
            'users', 'passkeyCounts', 'totpEnabled', 'lastLogin',
            'recentEvents', 'recentLockouts',
            'totalLogins', 'totalFailed', 'lockedNow', 'totalUsers', 'totalPasskeys',
            'search', 'filter', 'cred',
        ));
    }
}
