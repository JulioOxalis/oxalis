<?php
namespace Oxalis\Security;

use Illuminate\Support\Facades\Http;

/**
 * k-anonymity password breach check against the HaveIBeenPwned API.
 * Only the first 5 SHA-1 hex chars are sent to the API — the plaintext
 * password never leaves this server.
 */
class BreachCheckService
{
    private const API_BASE   = 'https://api.pwnedpasswords.com/range/';
    private const USER_AGENT = 'Oxalis-Auth/1.6 (https://github.com/JulioOxalis/oxalis)';

    public function isPwned(string $password): bool
    {
        try {
            $hash   = strtoupper(sha1($password));
            $prefix = substr($hash, 0, 5);
            $suffix = substr($hash, 5);

            $response = Http::withHeaders([
                'Add-Padding' => 'true',  // prevents traffic analysis
                'User-Agent'  => self::USER_AGENT,
            ])->timeout(3)->get(self::API_BASE . $prefix);

            if (!$response->successful()) {
                return false; // fail open — never block on API error
            }

            foreach (explode("\n", $response->body()) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                [$lineSuffix, $count] = explode(':', $line) + [null, '0'];
                if (strcasecmp((string) $lineSuffix, $suffix) === 0 && (int) $count > 0) {
                    return true;
                }
            }

            return false;
        } catch (\Throwable) {
            return false; // fail open
        }
    }
}
