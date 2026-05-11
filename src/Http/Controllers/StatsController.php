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

        // Method breakdown — PHP aggregation, works with MySQL and MongoDB
        $methods = $q()->where('status', 'success')
            ->get(['method'])
            ->groupBy('method')
            ->map(fn($g) => (object)['method' => $g->first()->method, 'total' => $g->count()])
            ->sortByDesc('total')
            ->values();

        $methodMax = $methods->max('total') ?: 1;

        // Last 7 days daily logins — PHP aggregation
        $recentEvents = $q()->where('status', 'success')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at']);

        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[$date] = $recentEvents->filter(
                fn($e) => \Carbon\Carbon::parse($e->created_at)->format('Y-m-d') === $date
            )->count();
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
