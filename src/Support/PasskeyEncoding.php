<?php
namespace Oxalis\Support;

/**
 * Base64url helpers for WebAuthn credential IDs (browser uses base64url, not standard base64).
 */
final class PasskeyEncoding
{
    public static function encode(string $binary): string
    {
        return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
    }

    public static function decode(string $base64url): string
    {
        $padded = strtr($base64url, '-_', '+/');
        $pad    = strlen($padded) % 4;
        if ($pad > 0) {
            $padded .= str_repeat('=', 4 - $pad);
        }

        return base64_decode($padded, true) ?: '';
    }

    /** Normalize any client credential id to the DB storage format (standard base64). */
    public static function credentialIdForStorage(string $rawIdFromClient): string
    {
        $binary = self::decode($rawIdFromClient);

        return $binary !== '' ? base64_encode($binary) : base64_encode(self::decode(base64_encode($rawIdFromClient)));
    }
}
