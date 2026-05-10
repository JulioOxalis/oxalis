<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\AuthEvent;
use Oxalis\Models\Passkey;
use Oxalis\Models\TotpSecret;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        $conn = config('oxalis.connection');
        $q    = fn() => AuthEvent::on($conn);

        $totalLogins  = $q()->where('status', 'success')->count();
        $totalFailed  = $q()->where('status', '!=', 'success')->count();
        $totalUsers   = $q()->where('status', 'success')->distinct('user_id')->count('user_id');
        $totalPasskeys = Passkey::on($conn)->count();
        $totpUsers    = TotpSecret::on($conn)->whereNotNull('confirmed_at')->count();

        $successRate = ($totalLogins + $totalFailed) > 0
            ? round($totalLogins / ($totalLogins + $totalFailed) * 100)
            : 100;

        // Method breakdown
        $methods = $q()->where('status', 'success')
            ->selectRaw('method, count(*) as total')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        $methodMax = $methods->max('total') ?: 1;

        // Last 7 days daily logins
        $daily = $q()->where('status', 'success')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw("DATE(created_at) as day, count(*) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[$date] = $daily[$date] ?? 0;
        }
        $dayMax = $days->max() ?: 1;

        // Recent events
        $recent = $q()->latest()->take(20)->get();

        return view('oxalis::stats.index', compact(
            'totalLogins', 'totalFailed', 'totalUsers',
            'totalPasskeys', 'totpUsers', 'successRate',
            'methods', 'methodMax', 'days', 'dayMax', 'recent'
        ));
    }
}
