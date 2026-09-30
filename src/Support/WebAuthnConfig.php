<?php
namespace Oxalis\Support;

use Illuminate\Http\Request;

/**
 * Origin / RP helpers used by the install wizard and health diagnostics.
 */
final class WebAuthnConfig
{
    /**
     * Build a sensible OXALIS_ORIGINS list for fresh installs (localhost variants + APP_URL).
     *
     * @return string[]
     */
    public static function suggestedOrigins(?string $appUrl = null, ?Request $request = null): array
    {
        $appUrl = rtrim($appUrl ?? (string) config('app.url', 'http://localhost'), '/');
        $parsed = parse_url($appUrl) ?: [];
        $scheme = $parsed['scheme'] ?? 'http';
        $host   = $parsed['host'] ?? 'localhost';
        $port   = $parsed['port'] ?? null;

        $origins = [self::buildOrigin($scheme, $host, $port)];

        if (in_array($host, ['localhost', '127.0.0.1'], true)) {
            foreach ([8000, 8080, 3000, 5173] as $devPort) {
                $origins[] = self::buildOrigin('http', 'localhost', $devPort);
                $origins[] = self::buildOrigin('http', '127.0.0.1', $devPort);
            }
            $origins[] = 'http://localhost';
            $origins[] = 'http://127.0.0.1';
        }

        if ($request) {
            $origins[] = $request->getSchemeAndHttpHost();
        }

        return array_values(array_unique(array_filter($origins)));
    }

    public static function originsCsv(?string $appUrl = null, ?Request $request = null): string
    {
        return implode(',', self::suggestedOrigins($appUrl, $request));
    }

    /**
     * @param  string[]  $configuredOrigins
     */
    public static function originMatchesRequest(array $configuredOrigins, Request $request): bool
    {
        $browser = $request->getSchemeAndHttpHost();

        foreach ($configuredOrigins as $origin) {
            if (strcasecmp(rtrim((string) $origin, '/'), $browser) === 0) {
                return true;
            }
        }

        return false;
    }

    public static function rpIdMatchesRequest(string $rpId, Request $request): bool
    {
        $host = $request->getHost();

        return $host === $rpId || str_ends_with('.'.$host, '.'.$rpId);
    }

    public static function buildOrigin(string $scheme, string $host, ?int $port): string
    {
        $defaultPorts = ['http' => 80, 'https' => 443];
        if ($port === null || (isset($defaultPorts[$scheme]) && $port === $defaultPorts[$scheme])) {
            return sprintf('%s://%s', $scheme, $host);
        }

        return sprintf('%s://%s:%d', $scheme, $host, $port);
    }
}
