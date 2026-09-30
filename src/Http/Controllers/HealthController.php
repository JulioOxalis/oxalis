<?php
namespace Oxalis\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Oxalis\Models\Passkey;
use Oxalis\Support\WebAuthnConfig;

/**
 * Public passkey configuration diagnostics (safe for production — no secrets).
 */
class HealthController extends Controller
{
    public function passkeys(Request $request)
    {
        $rpId    = (string) config('oxalis.rp_id', 'localhost');
        $origins = array_values(array_filter((array) config('oxalis.origins', [])));

        $originOk = WebAuthnConfig::originMatchesRequest($origins, $request);
        $rpOk     = WebAuthnConfig::rpIdMatchesRequest($rpId, $request);

        $tableOk  = false;
        $count    = null;
        try {
            $tableOk = Schema::hasTable('oxalis_passkeys');
            $count   = $tableOk ? Passkey::count() : null;
        } catch (\Throwable) {
            $tableOk = false;
        }

        $issues = [];
        if (! $originOk) {
            $issues[] = 'OXALIS_ORIGINS does not include '.$request->getSchemeAndHttpHost();
        }
        if (! $rpOk) {
            $issues[] = 'OXALIS_RP_ID ('.$rpId.') does not match browser host ('.$request->getHost().')';
        }
        if (! $tableOk) {
            $issues[] = 'oxalis_passkeys table missing — run php artisan migrate';
        }
        if (! config('oxalis.methods.passkey', true)) {
            $issues[] = 'OXALIS_ENABLE_PASSKEY is false';
        }

        return response()->json([
            'ok'               => $issues === [],
            'issues'           => $issues,
            'browser_origin'   => $request->getSchemeAndHttpHost(),
            'browser_host'     => $request->getHost(),
            'rp_id'            => $rpId,
            'origins'          => $origins,
            'origin_match'     => $originOk,
            'rp_id_match'      => $rpOk,
            'passkeys_table'   => $tableOk,
            'passkey_enabled'  => (bool) config('oxalis.methods.passkey', true),
            'registered_count' => $count,
            'suggested_origins'=> WebAuthnConfig::suggestedOrigins(null, $request),
        ]);
    }
}
